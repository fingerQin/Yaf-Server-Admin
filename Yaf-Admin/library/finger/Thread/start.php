<?php
/**
 * 示例。
 */

// 执行多线程业务处理.
$objThread = \finger\Thread\TaskThread::getInstance(10);
$objThread->setChildOverNewCreate(true);
$objThread->setRunDurationExit(30);
$objThread->start();
