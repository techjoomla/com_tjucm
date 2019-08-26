package testScript;

import org.testng.annotations.Test;
import org.testng.annotations.Test;

import Excel.Constant;
import Action.Admin;
import Action.frontEnd;
import config.BasicClass;

public class TestSuite {

	public class Module_TC01 extends BasicClass {

		@Test(priority = 1)
	    public void AdminLogInWithWrongCredential() throws Exception {
		driver.get(Constant.URL_admin);
			Admin.AdminLogInWithWrongCredential(driver);
		}
			
		@Test(priority = 2)
		public void AdminLogIn() throws Exception {
			driver.get(Constant.URL_admin);
			Admin.Login(driver);
		}
		
		@Test(priority = 3)
		private void CreateSubForm() throws Exception {
			Admin.CreateSubForm(driver);
		}
		
		@Test(priority = 4)
		private void Createtype() throws Exception {
			Admin.CreateType(driver);
		}
		
		@Test(priority = 5)
		private void CreateTextField()throws Exception{
			Admin.CreateTextField(driver);
		}
		
		
		@Test(priority = 6)
		private void CreateRadioField()throws Exception{
			Admin.CreateRadioField(driver);
		}
		
		@Test(priority = 7)
		private void CreateNumberField()throws Exception{
			Admin.CreateNumberField(driver);
		}
		
		@Test(priority = 8)
		private void CreateEmailField()throws Exception{
			Admin.CreateEmailField(driver);
		}
		
		@Test(priority = 9)
		private void CreateDateField()throws Exception{
			Admin.CreateDateField(driver);
		}
		
		@Test(priority = 10)
		private void CreateSingleSelectField()throws Exception{
			Admin.CreateSingleSelectField(driver);
		}
		
		@Test(priority = 11)
		private void CreateMultiSelectField()throws Exception{
			Admin.CreateMultiSelectField(driver);
		}
			
		@Test(priority = 12)
		private void CreateTextAreaField()throws Exception{
			Admin.CreateTextAreaField(driver);
		}
		
		@Test(priority = 13)
		private void CreateEditorField()throws Exception{
			Admin.CreateEditorField(driver);
		}
		
		@Test(priority = 14)
		private void CreateFileField()throws Exception{
			Admin.CreateFileField(driver);
		}
		
		@Test(priority = 15)
		private void CreateTextareaCounterField()throws Exception{
			Admin.CreateTextareaCounterField(driver);
		}
		
		@Test(priority = 16)
		private void CreateSQLField()throws Exception{
			Admin.CreateSQLField(driver);
		}
		
		@Test(priority = 17)
		private void CreateCheckboxField()throws Exception{
			Admin.CreateCheckboxField(driver);
		}
			
		@Test(priority = 18)
		private void CreateVideoField()throws Exception{
			Admin.CreateVideoField(driver);
		}
		
		@Test(priority = 19)
		private void CreateAudioField()throws Exception{
			Admin.CreateAudioField(driver);
		}
		
		@Test(priority = 20)
		private void CreateUCMSubForm()throws Exception{
			Admin.CreateUCMSubForm(driver);
		}
		
		@Test(priority = 21)
		private void CreateViewMenu()throws Exception{
			Admin.CreateViewMenu(driver);
		}
		
		@Test(priority = 22)
		private void CreateListMenu()throws Exception{
			Admin.CreateListMenu(driver);
		}
		@Test(priority = 23)
		private void Createuser()throws Exception {
			Admin.Createuser(driver);
		}
		//Starting with frontend 
		
		@Test(priority = 24)
		public void frontendLogInWithWrongCredential() throws Exception {
			driver.get(Constant.URL_front);
			frontEnd.frontendLogInWithWrongCredential(driver);
		}
		
		@Test(priority = 25)
		public void frontendLogIn() throws Exception {
			driver.get(Constant.URL_front);
			frontEnd.frontLogin(driver);
		}
		
		@Test(priority = 26)
		public void formFillnagative() throws Exception {
			frontEnd.formFillnagative(driver);
		}
		
		@Test(priority = 27)
		public void formFill() throws Exception {
			frontEnd.formFill(driver);
		}
		
	}
}