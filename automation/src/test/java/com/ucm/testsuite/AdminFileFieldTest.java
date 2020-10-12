package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminFileFieldPage;
public class AdminFileFieldTest extends BaseClass{
	@Test(dataProvider = "filefieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminFileField(String testdesc,String fl, String fn) throws Exception {
		AdminFileFieldPage filefield = new AdminFileFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			filefield.fileFieldCreation(fl,fn);
		}
	}
}