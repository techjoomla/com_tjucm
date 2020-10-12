package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminSingleSelectFieldPage;
public class AdminSingleSelectFieldTest extends BaseClass{
	@Test(dataProvider = "singleselectfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminSingleSelectField(String testdesc, String ssl, String nn, String cn1, String cv1, String cn2, String cv2, String cn3, String cv3, String cn4, String cv4) throws Exception {
		AdminSingleSelectFieldPage singleselectfield = new AdminSingleSelectFieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			singleselectfield.singleselectFieldCreation(ssl,nn,cn1,cv1,cn2,cv2,cn3,cv3,cn4,cv4);
		}
	}
}