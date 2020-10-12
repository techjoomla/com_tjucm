package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdmintcounterFieldPage;
public class AdmincounterFieldTest extends BaseClass{
	@Test(dataProvider = "tcounterfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintcounterField(String testdesc,String tl, String tn, String r, String c,String maxl, String minl, String h) throws Exception {
		AdmintcounterFieldPage tcounterfield = new AdmintcounterFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			tcounterfield.tcounterFieldCreation(tl,tn,r,c,maxl,minl,h);
		}
	}
}