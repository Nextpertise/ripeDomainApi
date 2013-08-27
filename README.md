RipeDomainAPI

Copyright (c) 2013 Nextpertise B.V.
Author: Teun Ouwehand <teun@nextpertise.nl>

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details <http://www.gnu.org/licenses/>

This program allows you to view, remove, add and modify information in the DOMAIN part of the RIPE database. We wrote this program to add our ds-rdata records to the RIPE database, which allows us to do a key rollover in the future.

Note: You need to add your ripe member password in ripeConfig.php

Usage:

	$ripeDomainApi = new ripeDamainApi();
	
	// Get zone information
	$ripeDomainApi->setZone('210.200.190.in-addr.arpa');
	$result = $ripeDomainApi->getZoneInfo();
	
	if($result) {
		print_r($result);
	} else {
		echo $ripeDomainApi->getLastError();
	}
	
	// Add a ds-rdata record to a domain
	$ripeDomainApi->setZone('210.200.190.in-addr.arpa');
	$ripeDomainApi->addAttribute('ds-rdata','61000 8 1 0ae1c3dbfcad5g3e7d3bc236e5185e0acf33e217');
	$result = $ripeDomainApi->addToZoneInfo();
	
	if($result) {
		print_r($result);
	} else {
		echo $ripeDomainApi->getLastError();
	}
	
	// Modify nameserver information of a domain
	$ripeDomainApi->setZone('210.200.190.in-addr.arpa');
	$ripeDomainApi->addAttribute('nserver','ns1.nextpertise.nl');
	$ripeDomainApi->addAttribute('nserver','ns2.nextpertise.nl');
	$result = $ripeDomainApi->modifyZoneInfo();
	
	if($result) {
		print_r($result);
	} else {
		echo $ripeDomainApi->getLastError();
	}
	
	// Remove attributes from a domain
	$ripeDomainApi->setZone('210.200.190.in-addr.arpa');
	$ripeDomainApi->addAttribute('ds-rdata','remove');
	$result = $ripeDomainApi->removeFromZoneInfo();
	
	if($result) {
		print_r($result);
	} else {
		echo $ripeDomainApi->getLastError();
	}