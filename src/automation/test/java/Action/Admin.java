package Action;

import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
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

	public static void AdminLogInWithWrongCredential(WebDriver driver) throws Exception {
		PageFactory.initElements(driver, adminObject.class);
		ExcellPath.setExcelFile(Constant.TestData_Path, "Sheet1");
		adminObject.username.sendKeys(ExcellPath.getCellData(99, 1));
		adminObject.password.sendKeys(ExcellPath.getCellData(100, 1));
		adminObject.login.click();
		System.out.println("==> Login fail due to wrong credential");
	}
	
	public static void Login(WebDriver driver) throws Exception
	
	{
		
		adminObject.username.sendKeys(ExcellPath.getCellData(0, 1));
		adminObject.password.sendKeys(ExcellPath.getCellData(0, 2));
		adminObject.login.click();
		System.out.println("==> Login Successfully");
	}
	
	public static void CreateSubForm (WebDriver driver)throws Exception {

		// create sub form 1st
		adminObject.newType.click();
		adminObject.titleName.sendKeys(ExcellPath.getCellData(70,1));
		adminObject.is_sub_form.click();
		adminObject.allow_count.sendKeys(ExcellPath.getCellData(74,1));
		adminObject.save.click();
		adminObject.permission.click();
		WebElement createsubItem = adminObject.permissionselect;
		Select s1= new Select(createsubItem);
		s1.selectByValue("1");
		
		adminObject.permission2.click();
		
		WebElement SubcreateItem = adminObject.permission_2;
		Select s2= new Select(SubcreateItem);
		s2.selectByValue("1");
		
		WebElement SubviewAllItem = adminObject.viewAll;  
		Select s3 = new Select(SubviewAllItem);
		s3.selectByValue("1");
		
		WebElement Subeditownitem = adminObject.EditOwnItem; 
		Select s4 = new Select(Subeditownitem);
		s4.selectByValue("1");
		adminObject.save_closetype.click();
		
		List<WebElement> NumberofTypesfield = adminObject.field_groupcount;
		for(int i=0;i<NumberofTypesfield.size();i++)
		{
			
			if(i==0)
			{
				WebElement singleFieldgroup = NumberofTypesfield.get(i);
				Thread.sleep(2000);
				singleFieldgroup.click();
			}
		}
		Thread.sleep(1000);
		adminObject.save_field_group.click();
		adminObject.group_name.sendKeys(ExcellPath.getCellData(71, 1));
		adminObject.save_groupname.click();
		adminObject.click_type.click();

		List<WebElement> NumberOfTypesForFieldssub = adminObject.field_typecount;
		for (int j=0; j<NumberOfTypesForFieldssub.size();j++)
		{
//			if(j==NumberOfTypesForFields.size()-1){if want to select the last field 
				if(j==0){
				WebElement singleField = NumberOfTypesForFieldssub.get(j); 
				Thread.sleep(2000);
				singleField.click();
			}
		}
		adminObject.click_field.click();
//		Create field for the form 
		adminObject.form_label.sendKeys(ExcellPath.getCellData(72, 1));
	    adminObject.form_name.sendKeys(ExcellPath.getCellData(74, 1)); 
	    adminObject.click_field_type.click();
		adminObject.select_field.click();
		adminObject.text_required.click();
		adminObject.saveandclose.click();
		adminObject.Type.click();
		System.out.println("==> Created SubForm");	
	}
	
	public static void CreateType (WebDriver driver)throws Exception {
		
		adminObject.newType.click();
		Thread.sleep(2000);
		adminObject.titleName.sendKeys(ExcellPath.getCellData(1,1));
//		adminObject.allow_count.sendKeys(ExcellPath.getCellData(74,1));
		driver.findElement(By.xpath("//input[@id='jform_allowed_count']")).sendKeys("0");
		adminObject.save.click();
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
			
			if(i==0)
			{
				WebElement singleFieldgroup = NumberofTypes.get(i);
				Thread.sleep(2000);
				singleFieldgroup.click();
			}
		}
		Thread.sleep(1000);
		adminObject.save_field_group.click();
		adminObject.group_name.sendKeys(ExcellPath.getCellData(2, 1));
		adminObject.save_groupname.click();
		adminObject.click_type.click();
				
		//create field 
		List<WebElement> NumberOfTypesForFields = adminObject.field_typecount;
		for (int j=0; j<NumberOfTypesForFields.size();j++)
		{
//			if(j==NumberOfTypesForFields.size()-1){if want to select the last field 
				if(j==0){
				WebElement singleField = NumberOfTypesForFields.get(j); 
				Thread.sleep(2000);
				singleField.click();
			}
		}
		adminObject.click_field.click();
		System.out.println("==> Created Type");
	}

		public static void CreateTextField (WebDriver driver)throws Exception {
		
		//		Create text field for first and last name
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.form_label.sendKeys(ExcellPath.getCellData(3, 1));
	    adminObject.form_name.sendKeys(ExcellPath.getCellData(4, 1)); 
	    adminObject.max_len.sendKeys(ExcellPath.getCellData(15, 1));
	    JavascriptExecutor js2 = (JavascriptExecutor) driver; // for scroll
	    js2.executeScript("window.scrollBy(0,10000)");
	    
	    adminObject.click_field_type.click();
		adminObject.select_field.click();
		adminObject.text_required.click();
		adminObject.text_save.click();
				
		adminObject.lable.sendKeys(ExcellPath.getCellData(5,1));
		adminObject.lastname.sendKeys(ExcellPath.getCellData(6, 1));
		adminObject.click_field_type.click();
		adminObject.selectfield1.click();
		adminObject.text_required.click(); 
		adminObject.text_save.click();
		System.out.println("==> Stared creating fields");
	}
		
		public static void CreateRadioField (WebDriver driver)throws Exception {

			// create radio field for gender and check validation
		adminObject.click_field_type.click();
		adminObject.select_radio.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(7,1));
		adminObject.radio_name.sendKeys(ExcellPath.getCellData(8,1));	
		adminObject.click_field_type.click();
		adminObject.select_radio.click();
		adminObject.clickAtPlushIcon.click();		
		adminObject.optionvalue1.sendKeys(ExcellPath.getCellData(9,1));
		adminObject.optionname1.sendKeys(ExcellPath.getCellData(10,1));
		adminObject.clickAtPlushIcon.click();
		adminObject.optionvalue2.sendKeys(ExcellPath.getCellData(11,1));
		adminObject.optionname2.sendKeys(ExcellPath.getCellData(12,1));
		adminObject.text_save.click();
	}
		public static void CreateNumberField (WebDriver driver)throws Exception {
			// create number field for phone number and check validation
		adminObject.click_field_type.click();
		adminObject.select_number.click();	
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(13,1));
		adminObject.numbername.sendKeys(ExcellPath.getCellData(14,1));
		adminObject.click_field_type.click();
		adminObject.select_number.click();
		adminObject.minNumber.sendKeys(ExcellPath.getCellData(15,1));
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateEmailField (WebDriver driver)throws Exception {
			//create email field  and check validation
		adminObject.click_field_type.click();
		adminObject.select_email.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(16, 1));
		adminObject.emailname.sendKeys(ExcellPath.getCellData(17, 1));
		adminObject.click_field_type.click();
		adminObject.select_email.click();
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateDateField (WebDriver driver)throws Exception {
			//create date field and check validation
		adminObject.click_field_type.click();
		adminObject.select_clender.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(75,1));
		adminObject.datename.sendKeys(ExcellPath.getCellData(75, 1));
		adminObject.click_field_type.click();
		adminObject.select_clender.click();
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateSingleSelectField (WebDriver driver)throws Exception {
			//create single select for country dropdown and check validation
		adminObject.click_field_type.click();
		adminObject.select_singl.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(18, 1));
		adminObject.Nationalityname.sendKeys(ExcellPath.getCellData(19, 1));
		adminObject.click_field_type.click();
		adminObject.select_singl.click();
		adminObject.clickAtPlushIcon.click();
		adminObject.countryname1.sendKeys(ExcellPath.getCellData(20, 1));
		adminObject.countryvalue1.sendKeys(ExcellPath.getCellData(21, 1));
		adminObject.clickAtPlushIcon.click();
		adminObject.countryname2.sendKeys(ExcellPath.getCellData(22, 1));
		adminObject.countryvalue2.sendKeys(ExcellPath.getCellData(23, 1));
		adminObject.clickAtPlushIcon.click();
		adminObject.countryname3.sendKeys(ExcellPath.getCellData(24, 1));
		adminObject.countryvalue3.sendKeys(ExcellPath.getCellData(25, 1));
		adminObject.clickAtPlushIcon.click();
		adminObject.countryname4.sendKeys(ExcellPath.getCellData(26, 1));
		adminObject.countryvalue4.sendKeys(ExcellPath.getCellData(27, 1));
		adminObject.text_save.click();
	}
		//	not working
		public static void CreateMultiSelectField (WebDriver driver)throws Exception {
			// create multiselect for selecting languages
		adminObject.lable.sendKeys(ExcellPath.getCellData(28, 1));
		adminObject.languagevalue.sendKeys(ExcellPath.getCellData(29, 1));
		adminObject.click_field_type.click();
		adminObject.select_multiselect.click();
		
		adminObject.clickAtPlushIcon.click();
		adminObject.languageName1.sendKeys(ExcellPath.getCellData(30, 1));
		adminObject.languageValue1.sendKeys(ExcellPath.getCellData(31, 1));
		adminObject.clickAtPlushIcon.click();
		adminObject.languageName2.sendKeys(ExcellPath.getCellData(32, 1));
		adminObject.languageValue2.sendKeys(ExcellPath.getCellData(33, 1));
		adminObject.clickAtPlushIcon.click();
		adminObject.languageName3.sendKeys(ExcellPath.getCellData(34, 1));
		adminObject.languageValue3.sendKeys(ExcellPath.getCellData(35, 1));
		adminObject.text_save.click();
	}
		public static void CreateTextAreaField (WebDriver driver)throws Exception {
			//create text area and check validation 
		adminObject.click_field_type.click();
		adminObject.selectTextarea.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(36, 1));
		adminObject.textareaname.sendKeys(ExcellPath.getCellData(37, 1));
		adminObject.click_field_type.click();
		adminObject.selectTextarea.click();
		adminObject.row20.sendKeys(ExcellPath.getCellData(38,1));
		adminObject.coloum20.sendKeys(ExcellPath.getCellData(39, 1));
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateEditorField (WebDriver driver)throws Exception {
			// create editor field and check validation 
		adminObject.click_field_type.click();
		adminObject.selectseditor.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(40, 1));
		adminObject.editorname.sendKeys(ExcellPath.getCellData(41, 1));
		adminObject.click_field_type.click();
		adminObject.selectseditor.click();
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateFileField (WebDriver driver)throws Exception {
			// create file field and check validation
		adminObject.click_field_type.click();
		adminObject.selectFile.click();
		adminObject.saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		adminObject.lable.sendKeys(ExcellPath.getCellData(42, 1));
		adminObject.fileName.sendKeys(ExcellPath.getCellData(43, 1));
		adminObject.click_field_type.click();
		adminObject.selectFile.click();
		adminObject.file_accpted.sendKeys(ExcellPath.getCellData(44, 1));
		adminObject.text_save.click();
	}
		public static void CreateTextareaCounterField (WebDriver driver)throws Exception {
			// create textarea counterfield
		adminObject.lable.sendKeys(ExcellPath.getCellData(45, 1));
		adminObject.cctextname.sendKeys(ExcellPath.getCellData(46, 1));
		adminObject.click_field_type.click();
		adminObject.selecttextareacc.click();
		adminObject.row20.sendKeys(ExcellPath.getCellData(38,1));
		adminObject.coloum20.sendKeys(ExcellPath.getCellData(39, 1));
		adminObject.max_len.sendKeys("100");
		adminObject.min_len.sendKeys("10");
		adminObject.hint.sendKeys(ExcellPath.getCellData(49, 1));
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateSQLField (WebDriver driver)throws Exception {
			// create sql field
		adminObject.lable.sendKeys(ExcellPath.getCellData(52, 1));
		adminObject.sqlname.sendKeys(ExcellPath.getCellData(53,1));
		adminObject.click_field_type.click();
		adminObject.selectSql.click();
		adminObject.sendSQL.sendKeys(ExcellPath.getCellData(54, 1));
		adminObject.text_required.click();
		adminObject.text_save.click();
		}	
		public static void CreateCheckboxField (WebDriver driver)throws Exception {
			// create checkbox field 
		adminObject.lable.sendKeys(ExcellPath.getCellData(55, 1));
		adminObject.checkboxname.sendKeys(ExcellPath.getCellData(56, 1));
		adminObject.click_field_type.click();
		adminObject.selectcheckbox.click();
		adminObject.text_required.click();
		adminObject.text_save.click();
	}
		public static void CreateVideoField (WebDriver driver)throws Exception {
			// create video field
		adminObject.lable.sendKeys(ExcellPath.getCellData(64,1));
		adminObject.lastname.sendKeys(ExcellPath.getCellData(65, 1));
		adminObject.click_field_type.click();
		adminObject.selectVideo.click();
		adminObject.text_required.click(); 
		adminObject.text_save.click();
	}
		public static void CreateAudioField (WebDriver driver)throws Exception {
			// create Audio field
		adminObject.lable.sendKeys(ExcellPath.getCellData(66,1));
		adminObject.lastname.sendKeys(ExcellPath.getCellData(67, 1));
		adminObject.click_field_type.click();
		adminObject.selectAudio.click();
		adminObject.text_required.click(); 
		adminObject.text_save.click();
		}
		public static void CreateUCMSubForm (WebDriver driver)throws Exception {
			//create UCM SubForm
		adminObject.lable.sendKeys(ExcellPath.getCellData(50, 1));
		adminObject.subformName.sendKeys(ExcellPath.getCellData(51, 1));
		adminObject.click_field_type.click();
		adminObject.select_ucm_subform.click();
		adminObject.singlesubForm.click();
		adminObject.selectsubform.click();
		adminObject.text_required.click();
		adminObject.saveandclose.click();			
	}
	
	public static void CreateViewMenu (WebDriver driver)throws Exception {
	    	// create menu for view the form
		adminObject.click_menu.click();
		adminObject.click_allmenu.click();
		adminObject.click_newmenu.click();
		adminObject.menu_name.sendKeys(ExcellPath.getCellData(57, 1));
		adminObject.menu_type.click();
	    adminObject.select_mainmenu.click();
		adminObject.create_viewformaccess.click();
		adminObject.select_register.click();
		adminObject.select_button_primary.click();
		
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		Thread.sleep(1000);
		adminObject.select_header.click();
		Thread.sleep(2000);
		adminObject.show_edit_text.click();	
		driver.switchTo().defaultContent();
		adminObject.ucm_config.click();
		adminObject.select_ucmtype.click();
		adminObject.select_typeTitle.click();
		Thread.sleep(3000);
		adminObject.saveandclose.click();
		adminObject.click_newmenu.click();
		System.out.println("==> Created View Menu");
	}
	public static void CreateListMenu (WebDriver driver)throws Exception {
			// create menu for list menu
		Thread.sleep(3000);
		adminObject.create_viewform.sendKeys(ExcellPath.getCellData(58, 1));
		adminObject.create_viewformaccess.click();
		adminObject.create_activeresult.click();				
		adminObject.select_button_primary.click();
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		Thread.sleep(1000);
		adminObject.select_header.click();
		Thread.sleep(2000);
		adminObject.create_showlist.click();	
		driver.switchTo().defaultContent(); //close the iFrame
		adminObject.ucm_config.click();
		adminObject.select_ucmtype.click();
		adminObject.select_typeTitle.click();
		Thread.sleep(5000);
		adminObject.save_groupname.click();
		System.out.println("==> Created List Menu");
	}
	
	public static void Createuser (WebDriver driver)throws Exception {
		adminObject.select_menuUser.click();
		adminObject.select_menuManager.click();
		adminObject.save_field_group.click();
		adminObject.numbername.sendKeys(ExcellPath.getCellData(59, 1));
		adminObject.insert_username.sendKeys(ExcellPath.getCellData(60, 1));
		adminObject.user_email.sendKeys(ExcellPath.getCellData(60, 1));
		adminObject.pwd1.sendKeys(ExcellPath.getCellData(61, 1));
		adminObject.pwd2.sendKeys(ExcellPath.getCellData(62, 1));
		adminObject.create_user.click();
		
	}

}
