package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminTextFieldPage;
public class AdminTextFieldTest extends BaseClass{
	@Test(dataProvider = "textfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintextField(String testdesc,String l,String tn, String ml) throws Exception {
		AdminTextFieldPage textfield = new AdminTextFieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			textfield.textFieldCreation(l,tn,ml);
		}
	}
}