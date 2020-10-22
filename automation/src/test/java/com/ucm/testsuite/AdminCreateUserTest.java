package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminCreateUserPage;
public class AdminCreateUserTest extends BaseClass{
	@Test(dataProvider = "usercreation", dataProviderClass = DataProviderClass.class)
	public void AdminCreateUser(String testdesc,String un, String iu, String ue, String pone, String ptwo) throws Exception {
		AdminCreateUserPage createuser = new AdminCreateUserPage(driver);
		
		if (testdesc.equals("validdata")) {
			createuser.ViewCreateUser(un,iu,ue,pone,ptwo);
		}
	}
}

