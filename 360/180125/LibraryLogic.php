<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LibraryLogic extends CI_Logic
{
    const BATCH_INSERT_COUNT = 500;
    public $_key_format      = array(
        'md5'        => 'file_md5',
        'lastupdate' => 'last_update',
        'filename'   => 'file_name',
        'filesize'   => 'file_size',
        'tbname'     => 'tbl_name',
    );
    public function __construct()
    {
        $this->load->model("LibraryModel");
    }

    public function getKeyword($filters = null, $limit, $offset, $sort_col, $is_asc)
    {
        $ret          = array('total' => 0, 'has_next' => false, 'list' => array(), 'count' => 0);
        $ret['total'] = $this->LibraryModel->getKeywordListCount($filters);
        $rows         = $this->LibraryModel->getKeywordList($filters, $limit + 1, $offset, $sort_col, $is_asc);

        if (($count = count($rows)) > $limit) {
            $ret['has_next'] = true;
            array_pop($rows);
        }
        $ret['list']  = $rows;
        $ret['count'] = count($rows);
        return $ret;
    }

    public function getRegular($filters = null, $limit, $offset, $sort_col, $is_asc)
    {
        $ret          = array('total' => 0, 'has_next' => false, 'list' => array(), 'count' => 0);
        $ret['total'] = $this->LibraryModel->getRegularListCount($filters);
        $rows         = $this->LibraryModel->getRegularList($filters, $limit + 1, $offset, $sort_col, $is_asc);
        if (($count = count($rows)) > $limit) {
            $ret['has_next'] = true;
            array_pop($rows);
        }

        $ret['list']  = $rows;
        $ret['count'] = count($rows);
        return $ret;
    }

    public function addOrUpdateKeyword($post)
    {
        $this->db->trans_begin();
        try {
            if (isset($post['id']) && !empty($post['id'])) {
                $id = $post['id'];
                unset($post['id']);
                //将词典名称、描述更新到keyword表中
                $this->LibraryModel->updateKeyword($id, $post['name'], $post['description'], $post['density']);
                //删除id对应的词典内筒
                $this->LibraryModel->delKeywordDictByKid($id);

            } else {
                //将字典名称、描述添加到keyword表中
                $id = $this->LibraryModel->addKeyword($post['name'], $post['description'], $post['density']);
            }
            // p($post['dict_content']);
            //批量更新词典内筒
            $this->dealArrContentToBatchInsert($post['dict_content'], array('gid' => $id), 'keyword_dict', array('keyword', 'weight', 'use_wildcard'));
        } catch (Exception $e) {
            $this->db->trans_rollback();
            throw $e;
        }
        $this->db->trans_commit();
    }

    public function deleteKeyword($id, $is_del)
    {
        // p($id);
        // $id = $this->LibraryModel->getKeywordRowById($id);
        // p($id);
        // if ($is_del == 1) {
        return $this->LibraryModel->delKeyword($id);
        // }
        //     else {
        //         return $this->LibraryModel->updateKeywordInfo('status' => 0);
        //     }
        // }

    }

    public function deleteRegular($id, $is_del)
    {
        return $this->LibraryModel->delRegular($id);
    }

    public function addOrUpdateRegular($post)
    {
        if (isset($post['id']) && !empty($post['id'])) {
            // p($post['id']);
            $id = $post['id'];
            unset($post['id']);
            $bRet = $this->LibraryModel->updateRegularInfo($post, $id);
        } else {
            $bRet = $this->LibraryModel->addRegularInfo($post);
        }
        return $bRet;
    }

    /*
     ** 构造批量插入
     ** content      要解析的批量入库的二维数组
     ** additional   如果所有记录的某几列都是固定同样的值，通过该参数传入
     ** tbl_name     表名
     ** filter_col   additonal的每条记录不保证都有相同的key, 为了不在sql执行的时候报错，有必要进行下过滤，注意填的是和表中字段名一致的值
     */
    public function dealArrContentToBatchInsert(array $content, array $additional, $tbl_name, array $filter_col, $serial_id_name = null)
    {
        $num           = 0;
        $flag          = 0;
        $bind_vals     = array();
        $insert_values = array();
        $insert_fields = array();

        if (!empty($serial_id_name)) {
            $insert_fields[] = $serial_id_name;
        }

        foreach ($content as $row) {

            $insert_val_arr = array();
            $bind_vals_key  = '';
            if (!empty($serial_id_name)) {
                $bind_vals_key             = ":{$serial_id_name}_" . $num;
                $bind_vals[$bind_vals_key] = $num;
                $insert_val_arr[]          = $bind_vals_key;
            }
            foreach ($additional as $additional_key => $additional_val) {
                $bind_vals_key             = ":{$additional_key}_" . $num;
                $bind_vals[$bind_vals_key] = $additional_val;
                $insert_val_arr[]          = $bind_vals_key;
                if ($flag === 0) {
                    $insert_fields[] = $additional_key;
                }
            }
            foreach ($row as $col => $val) {
                $col = $this->formatKey($col);
                if (in_array($col, $filter_col)) {
                    if ($flag === 0 && !in_array($col, $insert_fields)) {
//避免INSERT INTO dlp_keyword_dict (kid,id,kid,keyword) VALUES……
                        $insert_fields[] = $col;
                    }
                    $bind_vals_key = ":{$col}_" . $num;
                    if (!isset($bind_vals[$bind_vals_key])) {
//避免(:kid_0,:id_0,:kid_0,……)这种情况
                        $bind_vals[$bind_vals_key] = $this->formatValue($col, $val);
                        $insert_val_arr[]          = $bind_vals_key;
                    }
                }
            }

            $insert_values[] = '(' . implode(',', $insert_val_arr) . ')';
            $flag            = 1;
            $num++;

            if ($num % self::BATCH_INSERT_COUNT == 0) {
                //批量插入
                $insert_value_str = implode(',', $insert_values);
                $this->_dao->batchInsert($insert_value_str, $bind_vals, $insert_fields, $tbl_name);
                $insert_values = array();
                $bind_vals     = array();
            }
        }

        if (!empty($bind_vals)) {
            //批量插入
            $insert_value_str = implode(',', $insert_values);
            $this->LibraryModel->batchInsert($insert_value_str, $bind_vals, $insert_fields, $tbl_name);
        }
    }

    public function formatKey($key)
    {
        return array_key_exists($key, $this->_key_format) ? $this->_key_format[$key] : $key;
    }

    public function formatValue($key, $value)
    {
        switch ($key) {
            case 'lastupdate':
            case 'update_time':
                $value = date('Y-m-d H:i:s', $value);
                break;

            case 'fields':
            case 'finger_ids':
                $value = '{' . implode(',', $value) . '}';
                break;

            default:
                # code...
                break;
        }
        return $value;
    }
}
