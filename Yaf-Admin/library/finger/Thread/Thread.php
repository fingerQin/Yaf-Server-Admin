<?php
/**
 * 多进程工具封装。
 * -- 1、本工具请确定 PHP 已经安装了 pcntl 与 posix 两个扩展。
 * -- 2、进程信号
 * SIGHUP  : 挂起信号,编号为1。一般用于告诉守护进程平滑重启服务器。
 * SIGQUIT : 退出信号,编号为3。
 * SIGTERM : 软件终止信号,编号为15。
 * SIGINT  : 中断信号,编号为2。当用户输入 Ctrl + C 时由终端驱动程序发送 INT 信号。
 * SIGKILL : 杀死/删除进程，编号为9。
 * 
 * @author fingerQin
 * @date 2017-09-15
 */

namespace finger\Thread;

abstract class Thread
{
    protected $masterPid           = 0;       // 主进程 ID。
    protected $runDurationExit     = 0;       // 子进程运行多久自动退出。0 代表不退出。0 用于那些多进程一次性运算业务。

    protected $isNewCreate         = true;    // 子进程结束之后是否新创建。
    protected $threadNum           = 10;      // 总的进程数量。
    protected $childCount          = 0;       // 当前子进程数量。 

    protected static $instance     = null;    // 当前对象实例。
    protected static $childProcess = [];      // 保存子进程ID与子进程编号。

    /**
     * 构造方法实现单例。 
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * 防止克隆导致单例失败。
     *
     * @return void
     */
    private function __clone(){}

    /**
     * 单例对象实现。
     * @param  integer $threadNum 线程数量。
     * @return instance
     */
    final public static function getInstance($threadNum = 10) 
    {
        if (self::$instance == null) {
            self::$instance = new static;
        }
        if ($threadNum > 0) {
            self::$instance->threadNum = $threadNum;
        }
        return self::$instance;
    }

    /**
     * 启动脚本.
     */
    final public function start()
    {
        if (function_exists('pcntl_fork')) {
            $this->masterPid = posix_getpid();
            $this->registerSignal();

            while(true) {
                $this->childCount++; // 子进程数量加1。
                // 如果当前子进程数量小于等于允许的进程数量或允许子进程结束新开子进程的情况则执行。
                if (($this->childCount <= $this->threadNum) || $this->isNewCreate == true) {
                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        exit('could not fork');
                    } else if ($pid > 0) {
                        $this->pushChildProcessId($pid);
                        // 直到当前进程数量达到指定的数量就阻塞主进程。直到有子进程退出。
                        if ($this->childCount >= $this->threadNum) {
                            // 如果已经达到指定的进程数量,则挂起当前主进程。直到有子进程退出才执行此
                            pcntl_wait($status);
                        }
                    } else {
                        $childProcessNum = $this->childCount % $this->threadNum;
                        $startTimeTsp    = time();
                        $this->run($this->threadNum, $childProcessNum, $startTimeTsp);
                        exit(0);
                    }
                }
            }
            exit(0);
        }
    }

    /**
     * 设置子进程运行多久自动退出。
     *
     * @param  int  $runDurationExit  运行时长。单位（秒）。0 代表子进程无时间限制。
     *
     * @return void
     */
    final public function setRunDurationExit($runDurationExit)
    {
        $this->runDurationExit = $runDurationExit;
    }

    /**
     * 检测主进程是否存活。
     *
     * @return bool
     */
    final public function detectMasterProcessAlive()
    {
        if (posix_kill($this->masterPid, 0)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 子进程检测是否需要退出。
     *
     * -- 根据父进程状态决定是否退出。
     * -- 如果子进程退出不再生成新的子进程。则子进程不限定运行时间。
     *
     * @param  int  $startTimeTsp  子进程启动时间戳。
     *
     * @return boolean
     */
    final protected function isExit($startTimeTsp)
    {
        // [1] 运行超过指定时长则退出。
        if ($this->isNewCreate && $this->runDurationExit > 0) {
            $diffTime = time() - $startTimeTsp;
            if ($diffTime >= $this->runDurationExit * 60) {
                exit(0);
            }
        }
        // [2] 主进程退出则退出。
        $status = $this->detectMasterProcessAlive();
        if (!$status) {
            exit(0);
        }
    }

    /**
     * 保存当前进程 ID 信息。
     * 
     * @param int $pid 子进程 ID。
     * @return void
     */
    final protected function pushChildProcessId($pid)
    {
        $num = $this->detectChildProcessAlive();
        if ($num > 0) {
            // 说明此子进程是后续有子进程退出之后替换之前进程用。
            self::$childProcess[$num] = $pid;
        } else {
            // 说明此子进程还是初始化的子进程。
            self::$childProcess[] = $pid;
        }
    }

    /**
     * 检测当前保存的子进程存活情况。
     * 
     * -- 当存在死亡进程，则返回已死亡进程的子进程 ID 编号。
     *
     * @return int
     */
    final protected function detectChildProcessAlive()
    {
        foreach (self::$childProcess as $num => $pid) {
            // 使用 posix_kill 需要当前进程所有者与被检测 $pid 所有者是同一个或者当前进程所有者拥有足够的权限。
            if (!posix_kill($pid, 0)) {
                return $num;
            } else {
                return 0;
            }
        }
    }

    /**
     * 设置子进程结束之后是否创建新的子进程。
     *
     * @param bool $isNewCreate true or false
     * @return void
     */
    final public function setChildOverNewCreate($isNewCreate)
    {
        $this->isNewCreate = $isNewCreate;
    }

    /**
     * 获取进程数。
     */
    final public function getThreadNum()
    {
        return $this->threadNum;
    }

    /**
     * 父进程注册信号。
     *
     * @return void
     */
    final public function registerSignal()
    {
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGHUP,  [$this, 'signalHandler']);
        pcntl_signal(SIGUSR1, [$this, 'signalHandler']);
        pcntl_signal(SIGCHLD, [$this, 'signalHandler']);
    }

    /**
     * 信号处理器。
     *
     * @param  int  $signo  信号量。
     *
     * @return void
     */
    final public function signalHandler($signo)
    {
        switch ($signo) {
            case SIGTERM: // 进程退出。
            case SIGHUP: // 重启进程。
            case SIGUSR1:
            case SIGCHLD:
            default:
                break;
        }
    }

    /**
     * 设置进程数。
     *
     * @param  int  $num  进程数量。
     * @return void
     */
    final public function setThreadNum($num)
    {
        $this->threadNum = $num;
    }

    /**
     * 抽象的业务方法。
     * 
     * @param  int  $threadNum     进程数量。
     * @param  int  $num           子进程编号。
     * @param  int  $startTimeTsp  子进程启动时间戳。
     * 
     * @return void
     */
    abstract public function run($threadNum, $num, $startTimeTsp);
}
