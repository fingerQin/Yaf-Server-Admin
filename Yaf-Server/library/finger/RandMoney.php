<?php
/**
 * 随机金额。
 * -- 用于模拟微信红包金额。
 * @author fingerQin
 * @deta 2018-10-29
 */

namespace finger;

class RandMoney
{
    /**
     * 红包金额(元)
     * 
     * -- 最小值:0.01。
     *
     * @var float
     */
    private $rewardMoney;

    /**
     * 红包数量
     *
     * @var int
     */
    private $rewardNum;

    /**
     * 分散度值
     * 
     * -- 1 ~ 10000
     * --- 最佳值 100
     *
     * @var int
     */
    private $scatter;

    /**
     * 执行红包生成算法
     *
     * @param  float  $rewardMoney  随机总金额。
     * @param  int    $rewardNum    拆分数量。
     * @param  int    $scatter      分散度值。
     * @return void
     */
    public function splitReward($rewardMoney, $rewardNum, $scatter = 100)
    {
        // 传入红包金额和数量。
        $this->rewardMoney = $rewardMoney;
        $this->rewardNum   = $rewardNum;
        $this->scatter     = $scatter;
        $this->realscatter = $this->scatter / 100;

        $avgRand = round(1 / $this->rewardNum, 4);
        $randArr = [];
        while (count($randArr) < $rewardNum) {
            $t = round(sqrt(mt_rand(1, 10000) / $this->realscatter));
            $randArr[] = $t;
        }
        $randAll   = round(array_sum($randArr) / count($randArr), 4);
        $mixrand   = round($randAll / $avgRand, 4);
        $rewardArr = [];
        foreach ($randArr as $key => $randVal) {
            $randVal     = round($randVal / $mixrand, 4);
            $rewardArr[] = round($this->rewardMoney * $randVal, 2);
        }
        sort($rewardArr);
        $rewardAll = array_sum($rewardArr);
        $rewardArr[$this->rewardNum - 1] = round($this->rewardMoney - ($rewardAll - $rewardArr[$this->rewardNum - 1]), 2);
        rsort($rewardArr);
        return $rewardArr;
    }
}