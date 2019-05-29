<?
class soapclientLocal extends nusoapclient  {

  function soapclientLocal($soapWsdl, $wsdl) {
    return parent::nusoapclient($soapWsdl,$wsdl);
  }

/*
	function getWrappedProxy($remote=true) {
    if ($remote===true) return $this->getProxy();
    else {
  		$r = rand();
  		$evalStr = $this->_getLocalProxyClassCode($r);
  		//$this->debug("proxy class: $evalStr";
  		// eval the class
  		eval($evalStr);
  		// instantiate proxy object
  		eval("\$proxy = new soap_proxy_$r('');");
  		// transfer current wsdl data to the proxy thereby avoiding parsing the wsdl twice
  		$proxy->endpointType = 'wsdl';
  		$proxy->wsdlFile = $this->wsdlFile;
  		$proxy->wsdl = $this->wsdl;
  		$proxy->operations = $this->operations;
  		$proxy->defaultRpcParams = $this->defaultRpcParams;
  		// transfer other state
  		$proxy->username = $this->username;
  		$proxy->password = $this->password;
  		$proxy->authtype = $this->authtype;
  		$proxy->proxyhost = $this->proxyhost;
  		$proxy->proxyport = $this->proxyport;
  		$proxy->proxyusername = $this->proxyusername;
  		$proxy->proxypassword = $this->proxypassword;
  		$proxy->timeout = $this->timeout;
  		$proxy->response_timeout = $this->response_timeout;
  		$proxy->http_encoding = $this->http_encoding;
  		$proxy->persistentConnection = $this->persistentConnection;
  		$proxy->requestHeaders = $this->requestHeaders;
  		$proxy->soap_defencoding = $this->soap_defencoding;
  		$proxy->endpoint = $this->endpoint;
  		$proxy->forceEndpoint = $this->forceEndpoint;
  		return $proxy;
    }
  }
*/

	/**
	* dynamically creates proxy class code
	*
	* @return   string PHP/NuSOAP code for the proxy class
	* @access   private
	*/
	function _getLocalProxyClassCode() {
		if ($this->endpointType != 'wsdl') {
			$evalStr = 'A proxy can only be created for a WSDL client';
			$this->setError($evalStr);
			return $evalStr;
		}
		$evalStr = '';
		foreach ($this->operations as $operation => $opData) {
			if ($operation != '') {
				// create param string and param comment string
				if (sizeof($opData['input']['parts']) > 0) {
					$paramStr = '';
					$paramArrayStr = '';
					$paramCommentStr = '';
					foreach ($opData['input']['parts'] as $name => $type) {
						$paramStr .= "\$$name, ";
						$paramArrayStr .= "'$name' => \$$name, ";
						$paramCommentStr .= "$type \$$name, ";
					}
					$paramStr = substr($paramStr, 0, strlen($paramStr)-2);
					$paramArrayStr = substr($paramArrayStr, 0, strlen($paramArrayStr)-2);
					$paramCommentStr = substr($paramCommentStr, 0, strlen($paramCommentStr)-2);
				} else {
					$paramStr = '';
					$paramCommentStr = 'void';
				}
				$opData['namespace'] = !isset($opData['namespace']) ? 'http://testuri.com' : $opData['namespace'];
				$evalStr .= "// $paramCommentStr
	function " . str_replace('.', '__', $operation) . "($paramStr) {
		\$params = array($paramArrayStr);
		return call_user_func_array(__FUNCTION__, \$params);
	}
	";
				unset($paramStr);
				unset($paramCommentStr);
			}
		}
		$evalStr = 'class soap_proxy  {
	'.$evalStr.'
  function getError() {return;}
}';

    //error_log($evalStr);  
		return $evalStr;
	}

}


?>
