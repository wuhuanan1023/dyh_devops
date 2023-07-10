<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait PageTrait 分页处理
 *
 * @package App\Traits
 */
trait PageTrait
{

    protected $default_page      = 1;
    protected $default_page_size = 10;

    /**
     * 计算分页总数
     *
     * @param int $total     总记录数
     * @param int $page_size 分页大小
     *
     * @return int 分页总数
     */
    protected function getPagingTotal(int $total, int $page_size)
    {
        return ($total === 0) ? 0 : (int)ceil((int)$total / (int)$page_size);
    }

    /**
     * 获取分页数据
     *
     * @param Builder $query 查询
     * @param callable|null $transform 回调用于对数据重组
     *                                 function($paginator){
     *                                      return $paginator;
     *                                 }
     * @param array $params 合并参数
     *
     * @param int $page 页码
     * @param int $page_size 页数据数
     * @return array
     */
    protected function getPagingRows(Builder $query, callable $transform = null, array $params = [], int $page = 0, int $page_size = 0)
    {

        $page      = $page ?: (app('request')->page ?? $this->default_page);
        $page_size = $page_size ?: (app('request')->page_size ?? $this->default_page_size);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($page_size, ['*'], 'page', $page);

        if (is_callable($transform)) {
            $paginator = call_user_func($transform, $paginator);
        }
        return array_merge([
            'rows'       => $paginator->getCollection()->toArray(),
            'page'       => (int)$paginator->currentPage(),
            'page_size'  => (int)$paginator->perPage(),
            'page_total' => (int)$paginator->lastPage(),
            'total'      => (int)$paginator->total(),
        ], $params);
    }
}
