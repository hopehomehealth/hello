<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LibraryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->logic('LibraryLogic');
    }

    public function actionGetKeyword()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            $post = array(
                'sort_col' => $this->getParamWithRange('sort_col', ['id', 'name'], 'id'),
                'is_asc'   => filter_var($this->getParam('asc', true), FILTER_VALIDATE_BOOLEAN),
                'offset'   => $this->getParam('start', 0),
                'limit'    => $this->getParam('limit', 4),
            );
            $filters        = array();
            $result['data'] = $this->LibraryLogic->getKeyword($filters, $post['limit'], $post['offset'], $post['sort_col'], $post['is_asc']);

        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionGetRegular()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            // $passVerify = $this->verifyParam($_REQUEST, array(
            //     array('sort', 'in', 'range' => array('name', 'last_update'), 'allowEmpty' => true),
            // ));
            $post = array(
                'sort_col' => $this->getParamWithRange('sort_col', ['id', 'name'], 'id'),
                'is_asc'   => filter_var($this->getParam('asc', true), FILTER_VALIDATE_BOOLEAN),
                'offset'   => $this->getParam('offset', 0),
                'limit'    => $this->getParam('limit', 5),
            );

            $filters        = array();
            $result['data'] = $this->LibraryLogic->getRegular($filters, $post['limit'], $post['offset'], $post['sort_col'], $post['is_asc']);

        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionAddOrUpdateKeyword()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            // $this->verifyParam($_REQUEST, array(
            //     array('name', 'required'),
            //     array('description', 'required'),
            //     array('dict_content', 'required'),
            //     array('operate', 'in', 'range' => array('add', 'update'), 'allowEmpty' => true),
            // ));
            $post = array(
                'name'         => $this->getParam('name', 'dsf'),
                'description'  => $this->getParam('description', 'dfe3ew'),
                'dict_content' => $this->getParam('dict_content', '[{"keyword":"人大","weight":"3","density":"0","use_wildcard":"1","use_complex":"1"},{"keyword":"dsf1","weight":"3","density":"0","use_wildcard":"1","use_complex":"1"}]'),
                'density'      => intval($this->getParam('density', 0)),
                'id'           => $this->getParam('id',0),
            );
            if (!is_array($post['dic_content'])) {
                $post['dict_content'] = json_decode($post['dict_content'], true);
            }
            // p($post);
            $result['data'] = $this->LibraryLogic->addOrUpdateKeyword($post);
        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionDelKeyword()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {

            $id     = $this->getParam('id', 1);
            $is_del = $this->getParamWithRange('is_del', array(0, 1), 0);
            // $this->verifyParam($_REQUEST, array(
            //     array(
            //         'field' => 'id',
            //         'rules' => "required",
            //     ),
            // ));
            $this->LibraryLogic->deleteKeyword($id, $is_del);
        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionDelRegular() //todo删除的时候检查是否已经在用

    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            $id     = $this->getParam('id', 1);
            $is_del = $this->getParamWithRange('is_del', array(0, 1), 0);
            // $passVerify = $this->verifyParam($_REQUEST, array(
            //     array(
            //         'field' => 'id',
            //         'rules' => 'required',
            //     ),
            // ));
            $this->LibraryLogic->deleteRegular($id, $is_del);

        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionGetKeywordDict()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            $id             = intval($this->getParam('id'));
            $result['data'] = $this->LibraryLogic->getKeywordDict($id);

        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

    public function actionAddOrUpdateRegular()
    {
        $result = Constants::$WEB_SUCCESS_RT;
        try {
            $post = array(
                'name'        => $this->getParam('name', 'sd'),
                'description' => $this->getParam('description', 'ds'),
                'express'     => $this->getParam('express', 'ds'),
                'density'     => $this->getParam('density', 0),
                'id'          => $this->getParam('id', 2),
            );

            // $this->verifyParam($post, array(
            //     array(
            //         'field' => 'name',
            //         'rules' => "required",
            //     ),
            //     array(
            //         'field' => 'desc',
            //         'rules' => "required",
            //     ),
            //     array(
            //         'field' => 'express',
            //         'rules' => "required",
            //     ),
            //     array(
            //         'field' => 'operate',
            //         'rules' => "required|in_list[add,update]",
            //     ),
            // ));
            // $this->LibraryLogic->checkRegular($express);
            // p($post['id']);
            $this->LibraryLogic->addOrUpdateRegular($post);

        } catch (Exception $e) {
            $result = $e;
        }
        $this->renderJson($result);
    }

}
