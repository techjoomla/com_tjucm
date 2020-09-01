/*
 * @package    TJ-UCM
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';
/** global: com_tjucm */
com_tjucm.Services.Items = new (com_tjucm.Services.Base.extend({
    checkCompatibilityUrl: window.tjSiteRoot + "index.php?option=com_tjucm&task=type.getCompatableUcmType",
    copyItemUrl: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.copyItem",
    getClusterFieldUrl: window.tjSiteRoot + "index.php?option=com_tjucm&&task=type.getClusterField",
    config: {
        headers: {}
    },
    response: {
        "success": "",
        "message": ""
    },
    chekCompatibility: function (currentUcmType, callback){
        this.config.processData = false;
        this.config.contentType = false;
        this.post(this.checkCompatibilityUrl, currentUcmType, this.config, callback);
    },
    getClusterField: function (currentUcmType, callback){
        this.config.processData = false;
        this.config.contentType = false;
        this.post(this.checkCompatibilityUrl, currentUcmType, this.config, callback);
    },
    copyItem: function (copyItemData, callback){
        this.config.processData = false;
        this.config.contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
        this.post(this.copyItemUrl, copyItemData, this.config, callback);
    }
}));
