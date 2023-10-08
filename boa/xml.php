<?php
/*
Author  : poetbi (poetbi@163.com)
Document: http://boasoft.top/docs/api/boa.xml.html
Licenses: Apache-2.0 (http://apache.org/licenses/LICENSE-2.0)
*/
namespace boa;

class xml extends base{
	protected $cfg = [
		'attr' => '@attr',
		'data' => '@data',
		'sign' => '.',
		'root' => false,
		'tag' => 'boa'
	];
	private $obj = null;
	private $value = [];
	private $current = 0;
	private $max = 0;

    public function __construct($cfg){
		parent::__construct($cfg);
		
		if(!function_exists('xml_parser_create')){
			msg::set('boa.error.6', 'xml_parser_create()');
		}
		
		if(!class_exists('XMLWriter', false)){
			msg::set('boa.error.6', 'XMLWriter');
		}
    }
	
	public function read($str){
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, true);

		$res = xml_parse_into_struct($parser, $str, $this->value);

		if($res === 0){
			$errno = xml_get_error_code($parser);
			$err = xml_error_string($errno);
			$line = xml_get_current_line_number($parser);
			$col = xml_get_current_column_number($parser);
			msg::set('boa.error.73', $line, $col, "[$errno]$err");
		}
		xml_parser_free($parser);

		$this->max = count($this->value);
		$arr = $this->read_xml(0);
		if($this->cfg['root']){
			$root = $this->value[0]['tag'];
			$attr = $this->value[0]['attributes'];
			if($attr){
				$data[$root] = [
					$this->cfg['attr'] => $attr,
					$this->cfg['data'] => $arr
				];
			}else{
				$data[$root] = $arr;
			}
			$arr = $data;
		}
		return $arr;
	}

	public function read_file($file){
		if(!file_exists($file)){
			msg::set('boa.error.2', $file);
		}else{
			$str = file_get_contents($file);
			return $this->read($str);
		}
	}

	private function read_xml($i){
		$arr = [];
		for($k = $i; $k < $this->max; $k++){
			if($k <= $this->current){
				$k = $this->current;
				continue;
			}else{
				$this->current = $k;
			}
			$v = $this->value[$k];

			switch($v['type']){
				case 'open':
					$sub = $this->read_xml($k + 1);
					if($v['attributes']){
						$sub = [
							$this->cfg['data'] => $sub,
							$this->cfg['attr'] => $v['attributes']
						];
					}

					if(array_key_exists($v['tag'], $arr)){
						$v['tag'] = $v['tag'] . $this->cfg['sign'] . $k;
					}
					$arr[$v['tag']] = $sub;
					break;

				case 'complete':
					if(array_key_exists($v['tag'], $arr)){
						$v['tag'] = $v['tag'] . $this->cfg['sign'] . $k;
					}

					if($v['attributes']){
						$arr[$v['tag']] = [
							$this->cfg['attr'] => $v['attributes'],
							$this->cfg['data'] => $v['value']
						];
					}else{
						$arr[$v['tag']] = $v['value'];
					}
					break;

				case 'close':
					return $arr;
					break;
			}
		}
	}

	public function write($arr){
		if(count($arr) > 1){
			$data[$this->cfg['tag']] = $arr;
			$arr = $data;
		}

		$this->obj = new \XMLWriter();
		$this->obj->openMemory();
		$this->obj->setIndent(true);
		$this->obj->setIndentString("\t");
		$this->obj->startDocument('1.0', 'UTF-8');
		$this->write_xml($arr);
		$this->obj->endDocument();

		$str = $this->obj->outputMemory();
		return $str;
	}

	public function write_file($arr, $file){
		$str = $this->write($arr);
		$res = boa::file()->write($file, $str);
		return $res;
	}

	private function write_xml($arr){
		foreach($arr as $k => $v){
			if(is_numeric($k)){
				$k = $this->cfg['tag']. intval($k);
			}

			if(strpos($k, $this->cfg['sign'])){
				$k = strstr($k, $this->cfg['sign'], true);
			}

			$this->obj->startElement($k);
			if(is_array($v)){
				$attr = $v[$this->cfg['attr']];
				if($attr){
					$this->write_xml_attr($attr);
					unset($v[$this->cfg['attr']]);
				}

				if(count($v) == 1){
					$data = null;
		
					if(array_key_exists($this->cfg['data'], $v)){
						$data = $v[$this->cfg['data']];
					}else{
						$data = $v[0];
					}

					if($data !== null){
						if(is_array($data)){
							$v = $data;
						}else{
							$this->write_xml_data($data);
							unset($v);
						}
					}
				}

				if($v){
					$this->write_xml($v);
				}
			}else{
				$this->write_xml_data($v);
			}
			$this->obj->endElement();
		}
	}

	private function write_xml_attr($v){
		if(is_array($v)){
			foreach($v as $name => $value){
				if(is_numeric($name)){
					$name = $value;
				}

				$this->obj->startAttribute($name);
				$this->obj->text($value);
			}
		}else{
			$this->obj->startAttribute($v);
			$this->obj->text($v);
		}
		$this->obj->endAttribute();		
	}

	private function write_xml_data($v){
		if(preg_match('/[<>&\'"]/', $v)){
			$this->obj->writeCData($v);
		}else{
			$this->obj->text($v);
		}
	}
}
?>
