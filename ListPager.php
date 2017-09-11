<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */

namespace ktree\grid;

use yii\helpers\Html;
use yii\widgets\LinkPager;
use Yii;

/**
 * LinkPager displays a list of hyperlinks that lead to different pages of target.
 *
 * LinkPager works with a [[Pagination]] object which specifies the total number
 * of pages and the current page number.
 *
 * Note that LinkPager only generates the necessary HTML markups. In order for it
 * to look like a real pager, you should provide some CSS styles for it.
 * With the default configuration, LinkPager should look good using Twitter Bootstrap CSS framework.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since  2.0
 */
class ListPager extends LinkPager
{
    public $pageSizeList = [];
    public $totalCount = 0;
    public $pageSize = false;
    public $prevPageLabel = '<span class="glyphicon glyphicon-chevron-left"></span>';
    public $nextPageLabel = '<span class="glyphicon glyphicon-chevron-right"></span>';

    public function init()
    {
        parent::init();
        $this->pageSizeList = [
            "10" => "10",
            "25" => "25",
            "50" => "50",
            "100" => "100",
            '500' => "500"
        ];
    }

    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();

        $buttons = [];
        $currentPage = $this->pagination->getPage();
        if ($this->pageSize) {
            $buttons[] = $this->renderPageSize();
        }
        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton(
                $firstPageLabel,
                0,
                $this->firstPageCssClass,
                $currentPage <= 0,
                false
            );
        }


        // // internal pages
        // list($beginPage, $endPage) = $this->getPageRange();
        // for ($i = $beginPage; $i <= $endPage; ++$i) {
        //     $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        // }
        // internal pages
        //list($beginPage, $endPage) = $this->getPageRange();
        $buttons[] = $this->renderPageFiled();
        // Page Size


        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton(
                $lastPageLabel,
                $pageCount - 1,
                $this->lastPageCssClass,
                $currentPage >= $pageCount - 1,
                false
            );
        }

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }

    /*
    * Return input filed to navigate pages
    */
    public function renderPageFiled()
    {
        $pagenation = [];
        $pageCount = $this->pagination->getPageCount();
        $currentPage = $this->pagination->getPage();
        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $pagenation[] = $this->renderPageButton(
                $this->prevPageLabel,
                $page,
                $this->prevPageCssClass,
                $currentPage <= 0,
                false
            );
        }

        $input = Html::input(
                'text',
                'page',
                $this->pagination->getPage() + 1,
                ['class' => 'grid-pager-input grid-pager-field form-control']
            ) . Html::tag('span', yii::t(
                'app',
                'of {pageCount}',
                [
                    'pageCount' => $this->pagination->getPageCount(),
                    'recordsCount' => $this->totalCount
                ]
            ), ['class'=>'grid-pager-content']);
        $pagenation[] = Html::tag('div', $input, ['class' => 'pager-form col-md-8 col-sm-6 col-xs-8']);

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $pagenation[] = $this->renderPageButton(
                $this->nextPageLabel,
                $page,
                $this->nextPageCssClass,
                $currentPage >= $pageCount - 1,
                false
            );
        }
        return Html::tag('li', implode("\n", $pagenation), ['class' => 'page-list col-sm-9 col-xs-9']);
    }

    /*
    * Returns page size dropdown.
    */
    public function renderPageSize()
    {
        $input = Html::dropDownList(
                'per-page',
                $this->pagination->getPageSize(),
                $this->pageSizeList,
                ['class' => 'grid-pager-field grid-pager-size form-control']
            ) . ' ' . Html::tag('span',yii::t('app', 'per page'),['class'=>'pagination-per-page-dropdown']);
        $form = Html::tag('div', $input, ['class' => 'pager-form']);
        return Html::tag('li', $form, ['class' => 'page-size col-sm-3 col-xs-3']);
    }

    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => (empty($class) ? $this->pageCssClass : $class).' col-md-2 col-sm-2 col-xs-2'];
        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            $options['disabled'] = $this->disabledPageCssClass;
            Html::addCssClass($options, 'btn');
            return Html::tag('button', $label, $options);
        }
        $linkOptions = $this->linkOptions;
        $linkOptions = ['class' => (empty($class) ? $this->pageCssClass : $class).' col-md-2 col-sm-2 col-xs-2'];
        $linkOptions['data-page'] = $page;

        return Html::a($label, $this->pagination->createUrl($page), $linkOptions);
    }
}
