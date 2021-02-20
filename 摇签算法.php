<?php
/**
 * Created by PhpStorm.
 * Date: 2020/8/27
 * Time: 12:33
 */

namespace App\Service;

class Lottery
{
    // 奖品列表
    protected $prizeList = [];

    // 奖池
    protected $prizePool = [];

    // 奖池总精度
    protected $poolPrecision;

    // 开奖的概率随机数 数值越大概率越大
    protected $randNum;

    // 开奖的最终随机数 数值介于指定奖品区间内
    protected $finalNum;

    // 开奖结果 几等奖
    protected $lotteryRet;

    // 奖品
    protected $prizeRet;

    public function __construct(?array $prizeList = [])
    {
        $this->initPrizeList($prizeList);

        $this->initPrizePool();
    }

    /**
     * 奖品列表
     *
     * @param array|null $prizeList
     */
    protected function initPrizeList(?array $prizeList = [])
    {
        // v 值越大 抽中的概率越大
        $this->prizeList = $prizeList ?: [
            '0' => ['id' => 1, 'prize' => '1-20签', 'odds' => 300, 'prizeZone' => [1, 20]],
            '1' => ['id' => 2, 'prize' => '21-40签', 'odds' => 3300, 'prizeZone' => [21, 40]],
            '2' => ['id' => 3, 'prize' => '41-60签', 'odds' => 3300, 'prizeZone' => [41, 60]],
            '3' => ['id' => 4, 'prize' => '61-80签', 'odds' => 3050, 'prizeZone' => [61, 80]],
            '4' => ['id' => 5, 'prize' => '81-100签', 'odds' => 50, 'prizeZone' => [81, 100]],
        ];
    }

    /**
     * 奖池
     */
    protected function initPrizePool()
    {
        $pool = [];

        foreach ($this->prizeList as $item) {
            $pool[$item['id']] = $item['odds'];
        }

        $this->prizePool     = $pool;
        $this->poolPrecision = array_sum($this->prizePool);
    }

    /**
     * 抽奖业务
     *
     * @return array
     * @throws \Exception
     *
     */
    public function bingo()
    {
        if (count($this->prizePool) < 1) {
            throw new \Exception('奖池不能为空', 0);
        }

        $this->lotteryRet = $this->calculate();

        $this->arrangePrizeResult();

        return [
            'lotteryRet' => $this->lotteryRet,
            'randNum'    => $this->randNum,
            'finalNum'   => $this->finalNum,
            'prizeRet'   => $this->prizeRet,
        ];
    }

    /**
     * 开奖算法
     *
     * @return int|string|null
     */
    protected function calculate()
    {
        $result = null;

        //概率数组总精度
        $arrSum = $this->poolPrecision;

        //概率数组循环
        foreach ($this->prizePool as $key => $vv) {
            $randNum = mt_rand(1, $arrSum);
            if ($randNum <= $vv) {
                $result = $key;

                $this->randNum = $randNum;
                break;
            } else {
                $arrSum -= $vv;
            }
        }

        if (is_null($result)) {
            $result = $this->calculate();
        }

        return $result;
    }

    /**
     * 组装开奖结果数据
     */
    protected function arrangePrizeResult()
    {
        foreach ($this->prizeList as $item) {
            if ($item['id'] === $this->lotteryRet) {
                list($min, $max) = $item['prizeZone'];

                if ($min > $max) {
                    throw new \Exception('prizeZone 区间值错误', 0);
                }

                $this->finalNum = mt_rand($min, $max);
                $this->prizeRet = $item['prize'];

                break;
            }
        }
    }

    /**
     * 抽奖低保  未抽中任何奖品时取概率最大的奖品
     *
     * @return false|int|string
     */
    public function allowance()
    {

    }
}

 $lottery = new \App\Service\Lottery();
 $data    = $lottery->bingo();