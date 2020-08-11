/*
 * @package    TJ-UCM
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2019 TechJoomla. All rights reserved
 * @license    GNU General Public License version 2, or later
 */
'use strict';
/** global: com_tjucm */
com_tjucm.Services.Item = new (com_tjucm.Services.Base.extend({
    createNewRecordUrl: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.save",
    saveFormDataUrl: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.saveFormData",
    autoSaveFieldDataUrl: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.saveFieldData",
    getRelatedFieldUpdatedOptionsUrl: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.getRelatedFieldOptions",
    getUpdatedRelatedFieldOptions: window.tjSiteRoot + "index.php?option=com_tjucm&format=json&task=itemform.getUpdatedRelatedFieldOptions",
    config: {
        headers: {}
    },
    response: {
        "success": "",
        "message": ""
    },
    create: function (ucmTypeData, callback){
        this.config.processData = false;
        this.config.contentType = false;
        this.config.async = false;
        this.post(this.createNewRecordUrl, ucmTypeData, this.config, callback);
    },
    saveFieldData: function (ucmFormData, callback) {
        this.config.processData = false;
        this.config.contentType = false;
        this.post(this.autoSaveFieldDataUrl, ucmFormData, this.config, callback);
    },
    getUpdatedRelatedFieldsOptions: function (tjUcmItemFormData, callback){
        this.post(this.getRelatedFieldUpdatedOptionsUrl, tjUcmItemFormData, this.config, callback);
    },
    getRelatedFieldOptions: function (tjUcmItemFormData, callback){
        this.config.processData = false;
        this.config.contentType = false;
        this.config.async = false;
        this.post(this.getUpdatedRelatedFieldOptions, tjUcmItemFormData, this.config, callback);
    },
    saveFormData: function (ucmFormData, callback) {
        this.config.processData = false;
        this.config.contentType = false;
        this.post(this.saveFormDataUrl, ucmFormData, this.config, callback);
    }
}));
