package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminMultiSelectFieldPage;
public class AdminMultiSelectFieldTest extends BaseClass{
	@Test(dataProvider = "multiselectfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminMultiSelectField(String testdesc, String ssl, String ln, String ln1, String lv1, String ln2, String lv2, String ln3, String lv3) throws Exception {
		AdminMultiSelectFieldPage multiselectfield = new AdminMultiSelectFieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			multiselectfield.multiselectFieldCreation(ssl,ln,ln1,lv1,ln2,lv2,ln3,lv3);
		}
	}
}