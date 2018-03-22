<?php

namespace libs\Utils;

/**
 * 分页工具
 *
 * @author chenqionghe
 * @package libs\Utils
 */
class Page
{
    /**
     * 获取分页参数
     *
     * @param $page
     * @param $totalCount
     * @param int $pageSize
     * @return array
     */
    public static function get($page, $totalCount, $pageSize = 30)
    {
        $page = self::getPage($page);
        $pageCount = self::getPageCount($totalCount, $pageSize);
        $next = self::getNext($page, $pageCount);
        $pre = self::getPre($page);
        $limit = self::getLimit($pageSize);
        $offset = self::getOffset($page, $pageSize);
        return [
            'page' => $page,//当前页数
            'pageSize' => $pageSize,//分页大小
            'totalCount' => $totalCount,//总数
            'pageCount' => $pageCount,//总页数
            'next' => $next,//下一页
            'pre' => $pre,//上一页
            'limit' => $limit,//limit
            'offset' => $offset,//offset
        ];
    }

    /**
     * 当前页
     *
     * @param $page
     * @return int
     */
    public static function getPage($page)
    {
        $page = (int)$page;
        return !$page ? 1 : $page;
    }

    /**
     * 总页数
     *
     * @param $totalCount
     * @param $pageSize
     * @return int
     */
    public static function getPageCount($totalCount, $pageSize)
    {
        $totalCount = $totalCount < 0 ? 0 : (int)$totalCount;
        return ceil($totalCount / $pageSize);
    }


    /**
     * 获取下一页
     *
     * @param $page
     * @param $pageCount
     * @return int
     */
    public static function getNext($page, $pageCount)
    {
        if ($pageCount > 1 && $page >= 0 && $page < $pageCount) {
            return $page + 1;
        } else {
            return 0;
        }
    }

    /**
     * 获取上一页
     *
     * @param $page
     * @return int
     */
    public static function getPre($page)
    {
        if ($page <= 1) {
            return 0;
        }
        return $page - 1;
    }


    /**
     * 获取offset
     *
     * @param $page
     * @param $pageSize
     * @return int
     */
    public static function getOffset($page, $pageSize)
    {
        return $pageSize < 1 ? 0 : ($page - 1) * $pageSize;
    }

    /**
     * 获取limit
     *
     * @param $pageSize
     * @return int
     */
    public static function getLimit($pageSize)
    {
        return $pageSize < 1 ? -1 : $pageSize;
    }


}
