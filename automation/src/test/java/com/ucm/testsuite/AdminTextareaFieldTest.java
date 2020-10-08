package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminTextareaFieldPage;
public class AdminTextareaFieldTest extends BaseClass{
	@Test(dataProvider = "textareafieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminTextareaField(String testdesc, String tl, String tan, String row, String col) throws Exception {
		AdminTextareaFieldPage textareafield = new AdminTextareaFieldPage(driver);
				
		if (testdesc.equals("validdata")) {
			textareafield.textareaFieldCreation(tl,tan,row,col);
		}
	}
}