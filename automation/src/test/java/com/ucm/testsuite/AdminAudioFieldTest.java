package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdmintAudioFieldPage;
public class AdminAudioFieldTest extends BaseClass{
	@Test(dataProvider = "audiofieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintAudioField(String testdesc,String al, String an) throws Exception {
		AdmintAudioFieldPage audiofield = new AdmintAudioFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			audiofield.audioFieldCreation(al,an);
		}
	}
}
