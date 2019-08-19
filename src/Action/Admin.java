package Action;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.PageFactory;
import java.util.List;
import org.openqa.selenium.WebElement;
import Excel.Constant;
import Excel.ExcellPath;
import pageObjects.adminObject;
import org.openqa.selenium.support.ui.Select;

public class Admin {
	
	WebDriver driver;
	public static void Login(WebDriver driver) throws Exception {
		
		PageFactory.initElements(driver, adminObject.class);
		ExcellPath.setExcelFile(Constant.TestData_Path, "Sheet1");
		adminObject.username.sendKeys(ExcellPath.getCellData(0, 1));
		adminObject.password.sendKeys(ExcellPath.getCellData(0, 2));
		adminObject.login.click();
		//return true;
	}
	
	public static void CreateType (WebDriver driver)throws Exception {
		Thread.sleep(3000);
		adminObject.components.click();
		adminObject.tjucm.click();
		adminObject.newType.click();
		adminObject.titleName.sendKeys(ExcellPath.getCellData(1,1));
		adminObject.permission.click();
		
		WebElement createItem = adminObject.permissionselect;
		Select c1= new Select(createItem);
		c1.selectByValue("1");
		
		adminObject.permission2.click();
		
		WebElement RcreateItem = adminObject.permission_2;
		Select c2= new Select(RcreateItem);
		c2.selectByValue("1");
		
		WebElement viewAllItem = adminObject.viewAll;  
		Select c3 = new Select(viewAllItem);
		c3.selectByValue("1");
		
		WebElement editownitem = adminObject.EditOwnItem; 
		Select c4 = new Select(editownitem);
		c4.selectByValue("1");
		
		adminObject.save_closetype.click();
		
		// create group
		
		List<WebElement> NumberofTypes = adminObject.field_groupcount;
		for(int i=0;i<NumberofTypes.size();i++)
		{
			
			if(i==NumberofTypes.size()-1)
			{
				WebElement singleFieldgroup = NumberofTypes.get(i);
				Thread.sleep(3000);
				singleFieldgroup.click();
			}
		}
		Thread.sleep(1000);
		adminObject.save_field_group.click();
		adminObject.group_name.sendKeys(ExcellPath.getCellData(2, 1));
		adminObject.save_groupname.click();
		adminObject.click_type.click();
		
	// Create fields
		
		List<WebElement> NumberOfTypesForFields = adminObject.field_typecount;
		for (int j=0; j<NumberOfTypesForFields.size();j++)
		{
			if(j==NumberOfTypesForFields.size()-1){
				WebElement singleField = NumberOfTypesForFields.get(j); 
				Thread.sleep(3000);
				singleField.click();
			}
		}
		adminObject.click_field.click();
//		Create field for the form 
		adminObject.form_label.sendKeys(ExcellPath.getCellData(3, 1));
	    adminObject.form_name.sendKeys(ExcellPath.getCellData(4, 1)); 
	    adminObject.click_field_type.click();
		adminObject.select_field.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(5,1));
		adminObject.lastname.sendKeys(ExcellPath.getCellData(6, 1));
		adminObject.click_field_type.click();
		adminObject.selectfield1.click();
		adminObject.text_required.click(); 
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(7,1));
		adminObject.radio_name.sendKeys(ExcellPath.getCellData(8,1));	
		adminObject.click_field_type.click();
		adminObject.select_radio.click();
		adminObject.optionvalue1.sendKeys(ExcellPath.getCellData(9,1));
		adminObject.optionname1.sendKeys(ExcellPath.getCellData(10,1));
		adminObject.radiobutton.click();
		adminObject.optionvalue2.sendKeys(ExcellPath.getCellData(11,1));
		adminObject.optionname2.sendKeys(ExcellPath.getCellData(12,1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(13,1));
		adminObject.numbername.sendKeys(ExcellPath.getCellData(14,1));
		adminObject.click_field_type.click();
		adminObject.select_number.click();
		adminObject.minNumber.sendKeys(ExcellPath.getCellData(15,1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(16, 1));
		adminObject.emailname.sendKeys(ExcellPath.getCellData(17, 1));
		adminObject.click_field_type.click();
		adminObject.select_email.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(17, 1));
		adminObject.datename.sendKeys(ExcellPath.getCellData(18, 1));
		adminObject.click_field_type.click();
		adminObject.select_clender.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(18, 1));
		adminObject.Nationalityname.sendKeys(ExcellPath.getCellData(19, 1));
		adminObject.click_field_type.click();
		adminObject.select_singl.click();
		adminObject.countryname1.sendKeys(ExcellPath.getCellData(20, 1));
		adminObject.countryvalue1.sendKeys(ExcellPath.getCellData(21, 1));
		adminObject.radiobutton.click();
		adminObject.countryname2.sendKeys(ExcellPath.getCellData(22, 1));
		adminObject.countryvalue2.sendKeys(ExcellPath.getCellData(23, 1));
		adminObject.radiobutton.click();
		adminObject.countryname3.sendKeys(ExcellPath.getCellData(24, 1));
		adminObject.countryvalue3.sendKeys(ExcellPath.getCellData(25, 1));
		adminObject.radiobutton.click();
		adminObject.countryname4.sendKeys(ExcellPath.getCellData(26, 1));
		adminObject.countryvalue4.sendKeys(ExcellPath.getCellData(27, 1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(28, 1));
		adminObject.languagevalue.sendKeys(ExcellPath.getCellData(29, 1));
		adminObject.click_field_type.click();
		adminObject.select_multiselect.click();
		adminObject.languageName1.sendKeys(ExcellPath.getCellData(30, 1));
		adminObject.languageValue1.sendKeys(ExcellPath.getCellData(31, 1));
		adminObject.radiobutton.click();
		adminObject.languageName2.sendKeys(ExcellPath.getCellData(32, 1));
		adminObject.languageValue2.sendKeys(ExcellPath.getCellData(33, 1));
		adminObject.radiobutton.click();
		adminObject.languageName3.sendKeys(ExcellPath.getCellData(34, 1));
		adminObject.languageValue3.sendKeys(ExcellPath.getCellData(35, 1));
		adminObject.radiobutton.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(36, 1));
		adminObject.textareaname.sendKeys(ExcellPath.getCellData(37, 1));
		adminObject.click_field_type.click();
		adminObject.selectTextarea.click();
		adminObject.row20.sendKeys(ExcellPath.getCellData(38,1));
		adminObject.coloum20.sendKeys(ExcellPath.getCellData(39, 1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(40, 1));
		adminObject.editorname.sendKeys(ExcellPath.getCellData(41, 1));
		adminObject.click_field_type.click();
		adminObject.selectseditor.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(42, 1));
		adminObject.fileName.sendKeys(ExcellPath.getCellData(43, 1));
		adminObject.click_field_type.click();
		adminObject.selectFile.click();
		adminObject.file_accpted.sendKeys(ExcellPath.getCellData(44, 1));
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(45, 1));
		adminObject.cctextname.sendKeys(ExcellPath.getCellData(46, 1));
		adminObject.click_field_type.click();
		adminObject.selecttextareacc.click();
		adminObject.row20.sendKeys(ExcellPath.getCellData(38,1));
		adminObject.coloum20.sendKeys(ExcellPath.getCellData(39, 1));
		adminObject.max_len.sendKeys(ExcellPath.getCellData(47,1));
		adminObject.min_len.sendKeys(ExcellPath.getCellData(48,1));
		adminObject.hint.sendKeys(ExcellPath.getCellData(49, 1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(50, 1));
		adminObject.subformName.sendKeys(ExcellPath.getCellData(51, 1));
		adminObject.click_field_type.click();
		adminObject.select_subform.click();
		adminObject.singlesubForm.click();
		adminObject.selectsubform.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
	
		adminObject.lable.sendKeys(ExcellPath.getCellData(52, 1));
		adminObject.sqlname.sendKeys(ExcellPath.getCellData(53,1));
		adminObject.click_field_type.click();
		adminObject.selectSql.click();
		adminObject.sendSQL.sendKeys(ExcellPath.getCellData(54, 1));
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
		
		adminObject.lable.sendKeys(ExcellPath.getCellData(55, 1));
		adminObject.checkboxname.sendKeys(ExcellPath.getCellData(56, 1));
		adminObject.click_field_type.click();
		adminObject.selectcheckbox.click();
		adminObject.text_required.click();
		adminObject.text_showonlist.click();
		adminObject.text_save.click();
	}
	
	public static void CreateMenu (WebDriver driver)throws Exception {
		adminObject.click_menu.click();
		adminObject.click_allmenu.click();
		adminObject.click_newmenu.click();
		
		adminObject.menu_name.sendKeys(ExcellPath.getCellData(57, 1));
		adminObject. menu_type.click();
	    adminObject.select_mainmenu.click();
		adminObject.menu_name.click();
		adminObject.select_register.click();
		adminObject.select_primary.click();
		
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		adminObject.select_header.click();
		adminObject.show_edit_text.click();	
		driver.switchTo().defaultContent();
		adminObject.ucm_config.click();
		adminObject.select_ucmtype.click();
		adminObject.select_typeTitle.click();
		Thread.sleep(3000);
		adminObject.text_save.click();
		adminObject.menu_details.click();
		
		Thread.sleep(3000);
		adminObject.create_viewform.sendKeys(ExcellPath.getCellData(58, 1));
			/*driver.findElement(By.xpath("//div[@id='jform_access_chzn']")).click();
		driver.findElement(By.xpath("//ul[@class='chzn-results']/li[text()='Main Menu']")).click(); */
		adminObject.create_viewformaccess.click();
		adminObject.create_activeresult.click();				
		adminObject.select_primary.click();
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		adminObject.select_header.click();
		//here is the issue
		Thread.sleep(5000);
		adminObject.create_showlist.click();	
		driver.switchTo().defaultContent(); //close the iFrame
		adminObject.ucm_config.click();
		adminObject.select_ucmtype.click();
		adminObject.select_typeTitle.click();
		Thread.sleep(5000);
		adminObject.save_groupname.click();
		
	}
	
	public static void Createuser (WebDriver driver)throws Exception {
		adminObject.select_menuUser.click();
		adminObject.select_menuManager.click();
		adminObject.save_field_group.click();
		adminObject.lable.sendKeys(ExcellPath.getCellData(59, 1));
		adminObject.insert_username.sendKeys(ExcellPath.getCellData(60, 1));
		adminObject.pwd1.sendKeys(ExcellPath.getCellData(61, 1));
		adminObject.pwd2.sendKeys(ExcellPath.getCellData(62, 1));
		adminObject.create_user.sendKeys(ExcellPath.getCellData(63, 1));
		adminObject.create_user.click();
	}

}
