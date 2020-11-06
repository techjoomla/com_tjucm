package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.FrontAdvanceSearchPage;
public class FrontAdvanceSearchTest extends BaseClass{
	@Test(dataProvider = "textcategorycreation", dataProviderClass = DataProviderClass.class)
	public void FrontSearchField(String testdesc,String catl, String catn) throws Exception {
		FrontAdvanceSearchPage search = new FrontAdvanceSearchPage(driver);
				
		if (testdesc.equals("validdata")) {
			search.aSearch(catl,catn);
		}
	}
}