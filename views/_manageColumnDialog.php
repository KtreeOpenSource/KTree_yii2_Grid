<?php
/**
 * @link      http://ktree.com/
 * @copyright Copyright (c) 2017 KTree.com.
 * @license   http://ktree.com/license
 */

?>
<div id="<?= $id ?>-manage-dialog"  class="modal-body grid-manage-dialogue" style="display:none">
                <div class="col-md-6 ">
                    <div class="box box-secondary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Yii::t('app', 'Hidden Columns') ?> </h3>
                        </div>
                        <div class="body">
                            <div class="column-search-box">
                                    <input class="form-control search-list" type="search" id="search_hidden_fields"
                                        placeholder="Search">
                            </div>
                            <div class="ul-sortable-class hidden-filed-container">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-secondary no-padding-horiz">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?= Yii::t('app', 'Visible Columns') ?> </h3>
                        </div>
                        <div class="body">
                            <div class="ul-sortable-class visible-filed-container">

                            </div>
                        </div>
                    </div>
                </div>
        <div class="clearfix"></div>
        <div class="box-footer">
            <button class="btn btn-primary save-manage-columns" type="submit"><?= Yii::t('app', 'Submit')?></button>
        </div>
 </div>
