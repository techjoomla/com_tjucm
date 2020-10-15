package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdmintVideoFieldPage;
public class AdminVideoFieldTest extends BaseClass{
	@Test(dataProvider = "videofieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintVideoField(String testdesc,String vl, String vn) throws Exception {
		AdmintVideoFieldPage videofield = new AdmintVideoFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			videofield.videoFieldCreation(vl,vn);
		}
	}
}