package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminucmformfieldPage;
public class AdminucmformfieldTest extends BaseClass{
	@Test(dataProvider = "ucmsubformfiledcreation", dataProviderClass = DataProviderClass.class)
	public void AdminucmformField(String testdesc,String l,String sfn) throws Exception {
		AdminucmformfieldPage ucmformfield = new AdminucmformfieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			ucmformfield.ucmformFieldCreation(l,sfn);
		}
	}
}