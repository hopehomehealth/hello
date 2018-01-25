<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LibraryModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->_keyword_table      = 'keyword';
        $this->_keyword_dict_table = 'keyword_dict';
        $this->_regular_table      = 'regular';
    }

    public function getKeywordListCount($filters = null)
    {
        $bind_vals = [];
        $sql       = sprintf("SELECT COUNT(*) AS cnt FROM %s", $this->_keyword_table);
//         p($sql);
        $row = $this->db->getRow($sql, $bind_vals);
        return $row['cnt'];
    }

    public function updateKeyword($id, $name, $description, $density)
    {
        $bind_vals = array(
            ':id'          => $id,
            ':name'        => $name,
            ':description' => $description,
            ':density'     => $density,
            ':update_time' => time(),
        );
        $sql = sprintf('UPDATE %s SET name = :name, description = :description, density = :density, update_time = :update_time WHERE id = :id', $this->_keyword_table);
        return $this->dbEdit($sql, $bind_vals);
    }

    public function delKeywordDictById($kid)
    {
        $bind_vals = array(':kid => $kid');
        $sql       = sprintf('DELETE FROM %s WHERE id = :id', $this->_keyword_table);
        return $this->dbEdit($sql, $bind_vals);
    }

    public function getKeywordRowById($id)
    {
        $bind_vals = array(':id' => $id);
        $sql       = sprintf('SELECT * FROM %s WHERE id = :id', $this->_table);
        return $this->db->getRow($sql, $bind_vals);
    }

    public function getKeywordList($filters = null, $limit, $offset, $sort_col = 'id', $is_asc = true)
    {
        $bind_vals = array();
        $sql       = sprintf("SELECT name, description, density, create_time, update_time FROM %s ORDER BY %s %s LIMIT %s OFFSET %s", $this->_keyword_table, $sort_col, $is_asc ? 'asc' : 'desc', $limit, $offset);
        return $this->db->getRows($sql);
    }

    public function getRegularList($filters = null, $limit, $offset, $sort_col = 'id', $is_asc = true)
    {
        $bind_vals = array();
        $sql       = sprintf('SELECT id, name, description, express, density, update_time FROM %s ORDER BY %s %s LIMIT %s OFFSET %s', $this->_regular_table, $sort_col, $is_asc ? 'asc' : 'desc', $limit, $offset);
//        p($sql);
        return $this->db->getRows($sql, $bind_vals);
    }

    public function getRegularListCount($filters = null)
    {
        $bind_vals = [];
        $sql       = sprintf('SELECT count(*) as cnt FROM %s', $this->_regular_table);
//        p($sql);
        $row = $this->db->getRow($sql, $bind_vals);
        return $row['cnt'];
    }

    public function addRegularInfo($set = array())
    {
        $set['create_time'] = (isset($set['create_time']) && !empty($set['create_time'])) ? $set['create_time'] : time();
        foreach ($set as $key => $value) {
            $bind_vals[':' . $key] = $value;
        }
        $set_k  = implode(', ', array_keys($set));
        $bind_k = implode(', ', array_keys($bind_vals));
        $sql    = sprintf("INSERT INTO %s (%s) VALUES (%s)", $this->_regular_table, $set_k, $bind_k);
        return $this->db->dbEdit($sql, $bind_vals);
    }

    public function updateRegularInfo($set, $id)
    {
        $set['update_time'] = time();
        // p($set);
        $bind_vals = array(':id' => $id);
        $sql_part  = array();
        foreach ($set as $key => $value) {
            $bind_vals[':' . $key] = $value;
            $sql_part[]            = $key . ' = :' . $key;
        }
        $sql_part = join(', ', $sql_part);
        // p($sql_part);
        $sql = sprintf('UPDATE %s SET %s WHERE id = :id', $this->_regular_table, $sql_part);
        // p($sql);
        return $this->db->dbEdit($sql, $bind_vals);
    }

    public function delKeyword($id)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = :id', $this->_keyword_table);
        // p($sql);
        return $this->db->dbEdit($sql, array(
            ':id' => $id,
        ));
    }

    public function delRegular($id)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = :id', $this->_regular_table);
        return $this->db->dbEdit($sql, array(
            ':id' => $id,
        ));
    }

    public function delKeywordDictByKid($kid)
    {
        $bind_vals = array(':kid' => $kid);
        $sql       = sprintf('DELETE FROM %s WHERE kid = :kid', $this->_keyword_dict_table);
        return $this->db->dbEdit($sql, $bind_vals);
    }

    public function addKeyword($name, $description, $density)
    {
        $bind_vals = array(
            ':name'        => $name,
            ':description' => $description,
            ':density'     => $density,
        );
        $sql = sprintf('INSERT INTO %s (name, description, density) VALUES (:name, :description, :density)', $this->_keyword_table);
        return $this->db->dbEdit($sql, $bind_vals);
    }

    public function batchInsert($insert_value_str, array $bind_vals, array $insert_fields, $tbl_name)
    {
        $fields = implode(',', $insert_fields);
        $sql    = sprintf('INSERT INTO %s (%s) VALUES %s', $tbl_name, $fields, $insert_value_str);
        return $this->db->dbEdit($sql, $bind_vals);
    }

    /*basemodel中的语句*/
    /*
     * 构建in语句
     * $values array
     * $bind_vals array
     * $pam_prefix string
     * return string
     */
    public function buildInClause(array $values, &$bind_vals, $pam_prefix = 'val_')
    {
        $params = array();
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                $param_name             = ':' . $pam_prefix . $key;
                $params[]               = $param_name;
                $bind_vals[$param_name] = $value;
            }
        }
        return implode(' , ', $params);
    }
}
