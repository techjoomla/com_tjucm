package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdmintSQLFieldPage;
public class AdminSqlFieldTest extends BaseClass{
	@Test(dataProvider = "sqlfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdmintsqlField(String testdesc,String sl, String sn, String ss) throws Exception {
		AdmintSQLFieldPage sqlfield = new AdmintSQLFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			sqlfield.sqlFieldCreation(sl,sn,ss);
		}
	}
}