package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdmintCheckboxFieldPage;
public class AdminCheckboxFieldTest extends BaseClass{
	@Test(dataProvider = "checkboxfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintcheckboxField(String testdesc,String bbl, String cb) throws Exception {
		AdmintCheckboxFieldPage checkboxfield = new AdmintCheckboxFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			checkboxfield.checkboxFieldCreation(bbl,cb);
		}
	}
}