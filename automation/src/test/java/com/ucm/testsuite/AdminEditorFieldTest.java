package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminEditorFieldPage;
public class AdminEditorFieldTest extends BaseClass{
	@Test(dataProvider = "editorfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminEditorField(String testdesc,String edl, String en) throws Exception {
		AdminEditorFieldPage editorfield = new AdminEditorFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			editorfield.editorFieldCreation(edl,en);
		}
	}
}