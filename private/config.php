<?php

class Config
{
	const baseCurrency = 'EUR';
	const numberOfRequests = 7;  # 2 pairs per requests. 6 requests means 12 pairs
	const waitBetweenRequest = 3;  # wait 3 seconds before doing next request
}
