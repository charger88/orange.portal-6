<?php

class OPDB_Config {
	
	public static $DBHost = 'localhost'; // Database server
	public static $DBUser = 'test'; // DB user's name
	public static $DBPass = 'sss'; // DB user's password
	public static $DBName = ''; // DB name
	public static $DBPort = '3306'; // Database port
	public static $DBType = 'mi'; // Database type
	public static $MCHost = null; // Memcache server
	public static $MCPort = null; // Memcache port
	public static $MCPrefix = ''; // Memcache prefix
	public static $MCTime1 = 60; // Memcache caching time
	public static $MCTime2 = 600; // Memcache caching time
	public static $MCTime3 = 3600; // Memcache caching time
	public static $TablesPrefix = ''; // Prefix for tables
	public static $CommonTables = array(); // Tables uses with common prefix
	public static $CommonPrefix = ''; // Prefix for tables, which uses without default prefix
	public static $DBDebugMode = false; // Queries and Errors writes into log.html

}