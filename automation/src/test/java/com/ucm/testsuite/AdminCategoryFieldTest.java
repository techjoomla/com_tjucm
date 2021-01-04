package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminCategoryFieldPage;
public class AdminCategoryFieldTest extends BaseClass{
	
	@Test(dataProvider = "textcategorycreation", dataProviderClass = DataProviderClass.class)
	public void AdmincategoryField(String testdesc,String catl, String catn) throws Exception {
		AdminCategoryFieldPage catfield = new AdminCategoryFieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			catfield.catFieldCreation(catl,catn);
		}
	}
}