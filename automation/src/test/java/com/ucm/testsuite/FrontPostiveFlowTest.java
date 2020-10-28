package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.FrontPostiveFlowPage;
public class FrontPostiveFlowTest extends BaseClass{
	@Test(dataProvider = "frontpostiveflow", dataProviderClass = DataProviderClass.class)
	public void FrontPostiveFlow(String testdesc,String fnf, String nf, String ve,String vd,String eu, String ays, String ui,String cl,String sv1, String vl,String al) throws Exception {
		FrontPostiveFlowPage frontpostive = new FrontPostiveFlowPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			frontpostive.PostiveFlow(fnf,nf,ve,vd,eu,ays,ui,cl,sv1,vl,al);
		}
	}
}