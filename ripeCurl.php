<?php

/*** 	
 *
 * @author: Teun Ouwehand <teun@nextpertise.nl>
 * @company: Nextpertise B.V.
 * @link: http://www.nextpertise.nl
 * @description: This class allows you to get/add/modify/remove ripe DOMAIN information.
 * 
 ***/
 class ripeDomainApi {
	private $attributes = array();
	private $zone = '';
	private $apiurl = '';
	private $xml = '';
	private $lastError = '';

	/*** 	
	 *
	 * @description: Call the ripeAPI on port HTTPS via curl
	 * 
	 ***/
	private function _curl() {
		$contenttype = 'application/xml';
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getApiurl());
		if($this->getAttributes())
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getXml());
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		// curl_setopt($ch, CURLOPT_VERBOSE, true);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: " . $contenttype, "Accept: " . $contenttype));
		
		return curl_exec($ch);
	}
	
	/*** 	
	 *
	 * @description:	Process the output of curl and return them in an array
	 *					If an errors occurs, $this->lastError will be updated.
	 * @return: array / false
	 * 
	 ***/
	private function _execute() {
		$output = array();
	
		if($this->getZone() == '') {
			$this->setLastError("Can't process zone information: Zone is not set");
			return false;
		}

		$execute = $this->_curl();
		if(substr($execute, 0, 1) != '<') {
			$this->setLastError($execute);
		} else {
			$zoneInfo = simplexml_load_string($execute);
		}

		// Check if object is in suspected format
		if(isset($zoneInfo->{'objects'}->{'object'}->{'attributes'}->{'attribute'})) {
			// Loop through objects
			foreach($zoneInfo->{'objects'}->{'object'}->{'attributes'}->{'attribute'} as $attribute) {
				// Check if object has a attribute with a name and a value
				if(isset($attribute->attributes()->name) && isset($attribute->attributes()->value)) {
					// Check if key already exist in array
					if(isset($output[(string) $attribute->attributes()->name])) {
						// Check if key is already upgraded to a array
						if(is_array($output[(string) $attribute->attributes()->name])) {
							// Add to array
							array_push($output[(string) $attribute->attributes()->name], (string) $attribute->attributes()->value);
						} else {
							// Upgrade to Array
							$output[(string) $attribute->attributes()->name] = array($output[(string) $attribute->attributes()->name], (string) $attribute->attributes()->value);
						}

					} else {
						// Add to key->value array
						$output[(string) $attribute->attributes()->name] = (string) $attribute->attributes()->value;										
					}
				}
			}
		} else {
			$output = false;
		}

		$this->clearAll();
		return $output;
	}
	
	/*** 	
	 *
	 * @description: Get zone information, this function is a wrapper for _execute with the correct Api URL.
	 * @return: array / false
	 * 
	 ***/
	public function getZoneInfo() {
		$this->setApiurl(Config::baseurl_lookup . $this->zone);
		return $this->_execute();
	}

	/*** 	
	 *
	 * @description: Modify zone information, this function is a wrapper for _execute with the correct Api URL and XML.
	 * @return: array / false
	 * 
	 ***/	
	public function modifyZoneInfo() {
		$this->setApiurl(Config::baseurl_modify . $this->zone . '?password=' . Config::password);

		if($this->getAttributes())
			foreach($this->getAttributes() as $key => $value)
				$xml_body = '<attribute name="'.$key.'" value="'.$value.'"/>';		
		
		$xml = '<whois-modify><replace attribute-type="'.$key.'"><attributes>';
		$xml .= $xml_body;
		$xml .='</attributes></replace></whois-modify>';

		$this->setXml($xml);	
		return $this->_execute();
	}
	
	/*** 	
	 *
	 * @description: Add attribute to zone information, this function is a wrapper for _execute with the correct Api URL and XML.
	 * @return: array / false
	 * 
	 ***/	
	public function addToZoneInfo() {
		$this->setApiurl(Config::baseurl_modify . $this->zone . '?password=' . Config::password);

		if($this->getAttributes())
			foreach($this->getAttributes() as $key => $value)
				$xml_body = '<attribute name="'.$key.'" value="'.$value.'"/>';
		
		$xml = '<whois-modify><add><attributes>';
		$xml .= $xml_body;
		$xml .='</attributes></add></whois-modify>';

		$this->setXml($xml);	
		return $this->_execute();
	}

	/*** 	
	 *
	 * @description: Remove attribute from zone information, this function is a wrapper for _execute with the correct Api URL and XML.
	 * @return: array / false
	 * 
	 ***/	
	public function removeFromZoneInfo() {
		$this->setApiurl(Config::baseurl_modify . $this->zone . '?password=' . Config::password);

		if($this->getAttributes())
			foreach($this->getAttributes() as $key => $value)
				$xml_body = '<remove attribute-type="'.$key.'"/>';
		
		$xml = '<whois-modify>';
		$xml .= $xml_body;
		$xml .='</whois-modify>';

		$this->setXml($xml);	
		return $this->_execute();
	}
	
	/*** 	
	 *
	 * @description: Add attribute to array and set attribute type. You can only update one type at the time.
	 * 
	 ***/
	public function addAttribute($key, $value) {
		if($key != '' && $value != '') {
			$this->attributes[$key] = $value;
			return true;
		}
		
		return false;
	}
	
	// Clear functions	
	public function clearAttributes() {
		$this->setAttributes(array());		
	}
	
	public function clearXml() {
		$this->setXml('');
	}
	
	public function clearZone() {
		$this->setZone('');
	}
	
	public function clearApiurl() {
		$this->setApiurl('');
	}
	
	public function clearAll() {
		$this->clearAttributes();
		$this->clearZone();
		$this->clearApiurl();
		$this->clearXml();
	}
	
	// Getters & Setters
	private function getAttributes() {
		return $this->attributes;
	}

	private function setAttributes($attributes) {
		$this->attributes = $attributes;
	}
	
	private function getXml(){
		return $this->xml;
	}

	private function setXml($xml){
		$this->xml = $xml;
	}
	
	private function setLastError($lastError){
		$this->lastError = $lastError;
	}
	
	// Public setters
	public function getLastError(){
		return $this->lastError;
	}

	private function getApiurl(){
		return $this->apiurl;
	}

	private function setApiurl($apiurl){
		$this->apiurl = $apiurl;
	}
	
	public function getZone(){
		return $this->zone;
	}

	public function setZone($zone){
		$this->zone = $zone;
	}
 }


?>