package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.FrontPostiveFlowPage;
public class FrontPostiveFlowTest extends BaseClass{
	@Test(dataProvider = "frontpostiveflow", dataProviderClass = DataProviderClass.class)
	public void FrontPostiveFlow(String testdesc,String fnf, String nf, String ve,String vd,String eu, String ays, String ui,String cl,String sv1, String vl,String al, String ui1) throws Exception {
		FrontPostiveFlowPage front = new FrontPostiveFlowPage(driver);
		
		if (testdesc.equals("negative")) {
			front.NagativeFlow(fnf,nf,ve,vd,eu,ays,ui,cl,sv1,vl,al,ui1);
		}		
		if (testdesc.equals("validdata")) {
			front.PostiveFlow(fnf,nf,ve,vd,eu,ays,ui,cl,sv1,vl,al);
		}
	}
}