<?php

namespace C\mod {

	use C\lib\Db;
	use C\lib\Sql;

	class mod {
		
		// --- 可编辑 ---
		protected static $__table_s = '';
		protected static $__primary_s = '';

		// --- 不可编辑 ---
		public $__primary = '';
		public $__table = '';
		protected $__updates = [];

		public function __construct() {
			$this->__table = static::$__table_s;
			$this->__primary = static::$__primary_s;
		}

		public function set($n, $v = '') {
			if(is_array($n)) {
				foreach ($n as $k => $v) {
					if ((isset($this->$k) && ($this->$k != $v)) || !isset($this->$k)) {
						$this->__updates[$k] = true;
						$this->$k = $v;
					}
				}
			} else {
				if ((isset($this->$n) && ($this->$n != $v)) || !isset($this->$n)) {
					$this->__updates[$n] = true;
					$this->$n = $v;
				}
			}
		}

		public function update() {
			$updates = [];
			foreach ($this->__updates as $k => $v)
				$updates[$k] = $this->$k;

			if(count($updates) > 0) {
				$pre = Db::bindPrepare($updates);
				$r = Db::prepare('UPDATE ' . DB_PRE . $this->__table . ' SET ' . $pre['sql'] . ' WHERE ' . $this->__primary . ' = :' . $this->__primary);
				$pre['arr'][':' . $this->__primary] = $this->{$this->__primary};
				if ($r->execute($pre['arr'])) {
					$this->__updates = [];
					return $r;
				} else
					return false;
			} else
				return true;
		}

		public function remove() {
			$r = Db::exec('DELETE FROM ' . DB_PRE . $this->__table .' WHERE ' . $this->__primary . ' = ' . $this->{$this->__primary});
			if ($r > 0)
				return true;
			else return false;
		}

		public static function delete($where) {
			$sql = new Sql();
			$sql->delete(static::$__table_s);
			if(is_array($where))
				$sql->where($where);
			else
				$sql->append(' WHERE '.$where);
			if(Db::exec($sql->sql)) {
				return true;
			} else
				return false;
		}

		public function create() {
			$updates = [];
			foreach ($this->__updates as $k => $v)
				$updates[$k] = $this->$k;

			$pre = Db::bindPrepare($updates);
			$r = Db::prepare('INSERT'.' INTO ' . DB_PRE . $this->__table . ' SET '. $pre['sql']);
			if ($r->execute($pre['arr'])) {
				$this->{$this->__primary} = Db::getInsertID();
				$p = Db::prepare('SELECT *'.' FROM '.DB_PRE.$this->__table.' WHERE '.$this->__primary.' = :'.$this->__primary);
				$p->execute([':'.$this->__primary=>$this->{$this->__primary}]);
				$a = $p->fetch(\PDO::FETCH_ASSOC);
				foreach($a as $k => $v)
					$this->$k = $v;
				$this->__updates = [];
				return $r;
			} else if (Db::getErrorCode() == 1062)
				return false;
			else {
				\C\log('[Db]' . print_r(Db::getErrorInfo(), true) . '(' . Db::getErrorCode() . ')');
				return false;
			}

		}

		public function toArray() {
			$rtn = [];
			foreach ($this as $key => $v)
				if ($key[0] != '_')
					$rtn[$key] = $v;
			return $rtn;
		}

		/**
		 * 需要数据库支持 is_remove、time_remove 字段
		 */
		public function softRemove() {
			Db::exec('UPDATE ' . DB_PRE . $this->__table .' SET is_remove = 1, time_remove = ' . $_SERVER['REQUEST_TIME'] . ' WHERE ' . $this->__primary . ' = ' . $this->{$this->__primary} . ' AND is_remove = 0');
			if (Db::getAffectRows() > 0) {
				$this->is_remove = '1';
				$this->time_remove = $_SERVER['REQUEST_TIME'];
				return true;
			} else return false;
		}

		// --- 静态方法 ---
		public static function get($where) {
			$mod = static::class;
			$sql = new Sql();
			$sql->select('*', static::$__table_s);
			if(is_array($where))
				$sql->where($where);
			else
				$sql->append(' WHERE '.$where);
			$ps = Db::query($sql->sql);
			if($obj = $ps->fetchObject($mod)) {
				return $obj;
			} else
				return false;
		}

		// --- 添加一个序列 ---
		public static function insert($cs, $vs) {

			$sql = new Sql();
			$sql->insert(static::$__table_s, $cs, $vs);
			$r = Db::exec($sql->sql);
			return $r == 0 ? false : true;

		}

		// --- 获取列表, 数组里面是 mod 对象 ---
		public static function getList($where, $limit = NULL, $by = NULL, $array = false) {

			$mod = static::class;
			$sql = new Sql();
			$sql->select('*', static::$__table_s);
			if(is_array($where))
				$sql->where($where);
			else
				$sql->append(' WHERE ' . $where);
			if($by !== NULL) $sql->by($by[0], $by[1]);
			if($limit !== NULL) $sql->limit($limit[0], $limit[1]);
			$ps = Db::query($sql->sql);
			$list = [];
			if ($array) {
				while ($obj = $ps->fetch(\PDO::FETCH_ASSOC))
					$list[] = $obj;
			} else {
				while ($obj = $ps->fetchObject($mod))
					$list[] = $obj;
			}
			return $list;
		}

	}

	trait modKey {

		/**
		 * This method insert a new row into table with a non-numerical
		 * primary key.
		 * @return bool
		 */
		public function create() {
			$updates = [];
			foreach ($this->__updates as $k => $v)
				$updates[$k] = $this->$k;

			$column = isset($this->__key) ? $this->__key : $this->__primary;

			do {
				$updates[$column] = $this->createKey();
				$pre = Db::bindPrepare($updates);
				$r = Db::prepare('INSERT'.' INTO ' . DB_PRE . $this->__table . ' SET '. $pre['sql']);
			} while (!($r->execute($pre['arr'])) && Db::getErrorCode() == 1062);

			if ($r) {
				$this->{$column} = $updates[$column];
				$p = Db::prepare('SELECT *'.' FROM '.DB_PRE.$this->__table.' WHERE '.$column.' = :'.$column);
				$p->execute([':'.$column=>$this->{$column}]);
				$a = $p->fetch(\PDO::FETCH_ASSOC);
				foreach($a as $k => $v)
					$this->$k = $v;
				return true;
			}

			return false;
		}

		abstract public function createKey();
	}

}
