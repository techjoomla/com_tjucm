package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminRadioFieldPage;
public class AdminRadioFieldTest extends BaseClass{
	@Test(dataProvider = "radiobuttoncreation", dataProviderClass = DataProviderClass.class)
	public void AdminRadioField(String testdesc,String l, String rn, String on1, String ov1, String on2, String ov2) throws Exception {
		AdminRadioFieldPage radiobutton = new AdminRadioFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			radiobutton.radioButtonCreation(l,rn,on1,ov1,on2,ov2);
		}
	}
}