<?php

class SubjectMatterExpert extends DataObject
{
    /**
     * @var array
     */
    private static $db = array(
        "Name" => "Varchar(255)",
        "Role" => "Varchar(255)",
        "BusinessUnit" => "Varchar(255)",
        "Agency" => "Varchar(255)",
        "Email" => "Varchar(255)"
    );

    /**
     * @var array
     */
    private static $has_one = array(
        "SiteTree" => "SiteTree",
    );

    /**
     * @var array
     */
    private static $summary_fields = array(
        "Name" => array("title" => "Name"),
        "Role" => array("title" => "Role"),
        "BusinessUnit" => array("title" => "Business Unit"),
        "Agency" => array("title" => "Agency"),
        "Email" => array("title" => "Email"),
    );

    /**
     * @var string
     */
    private static $default_sort = "Created DESC";

}
