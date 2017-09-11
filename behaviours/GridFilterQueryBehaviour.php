<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */
namespace ktree\grid\behaviours;

use yii\base\Behavior;
use yii\helpers\Json;
use ktree\grid\models\UserListPreference;

class GridFilterQueryBehaviour extends Behavior
{
    /**
     * @var array
     */
    public $filterQueryParams = [];

    public function filterQueryParams($searchModel, $queryParams)
    {
        if (count($this->filterQueryParams)) {
            return $this->filterQueryParams;
        }

        if (isset($queryParams[$searchModel]) || isset($queryParams['user_list_preference'])) {
            $existingFilters = (isset($queryParams['user_list_preference'])
                && !empty($queryParams['user_list_preference'])
            ) ?
                Json::decode(
                    UserListPreference::findOne($queryParams['user_list_preference'])->filters,
                    true
                ) : (isset($queryParams[$searchModel])?$queryParams[$searchModel]:'');

            $existingFilters = is_array($existingFilters) ? $existingFilters : [];

            $queryParams[$searchModel] = (isset($queryParams['is_user_list_preference'])&&$queryParams['is_user_list_preference'] != 0)
                ?
                $existingFilters
                : array_replace_recursive(
                    $existingFilters,
                    isset($queryParams[$searchModel])?$queryParams[$searchModel]:[]
                );
            $queryParams = self::arrayFilterRecursive($queryParams);
        }
        $this->filterQueryParams = $queryParams;
        return $queryParams;
    }

    /**
     * Recursively filter an array
     *
     * @param array $array
     *
     * @return array
     */
    public function arrayFilterRecursive(array $array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::arrayFilterRecursive($value);
                $value = array_filter(
                    $value,
                    function ($value) {
                        return ($value !== null && $value !== false && $value !== '');
                    }
                );
            } else {
                if ($value == '') {
                    unset($array[$key]);
                }
            }
        }
        return $array;
    }
}
