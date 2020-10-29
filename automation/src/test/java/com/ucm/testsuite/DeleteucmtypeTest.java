package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.DeleteucmtypePage;
public class DeleteucmtypeTest extends BaseClass{
	@Test(dataProvider = "deleteucm", dataProviderClass = DataProviderClass.class)
	public void DeleteUcm(String testdesc) throws Exception {
		DeleteucmtypePage deleteucm = new DeleteucmtypePage(driver);
		
		
		if (testdesc.equals("validdata")) {
			deleteucm.deleteucmtype();
		}
	}
}