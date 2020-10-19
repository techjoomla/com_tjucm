package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminViewMenuPage;
public class AdminViewMenuTest extends BaseClass{
	@Test(dataProvider = "menuviewcreation", dataProviderClass = DataProviderClass.class)
	public void AdminViewMenuField(String testdesc,String mn,String mn1) throws Exception {
		AdminViewMenuPage viewmenu = new AdminViewMenuPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			viewmenu.ViewMenuCreation(mn,mn1);
		}
	}
}

