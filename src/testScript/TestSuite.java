package testScript;

import org.testng.annotations.Test;

import Excel.Constant;
import Action.Admin;
import Action.frontEnd;
import config.BasicClass;

public class TestSuite {

	public class Module_TC01 extends BasicClass {

			@Test(priority = 1)
		public void AdminLogIn() throws Exception {
			driver.get(Constant.URL_admin);
			Admin.Login(driver);
		}
		
		@Test(priority = 2)
		private void CreateSubForm() throws Exception {
			Admin.CreateSubForm(driver);
		}
		
		@Test(priority = 3)
		private void Createtype() throws Exception {
			Admin.CreateType(driver);
		}
		
	
		@Test(priority = 4)
		private void CreateMenu()throws Exception{
			Admin.CreateMenu(driver);
		}
		
		@Test(priority = 5)
		private void Createuser()throws Exception {
			Admin.Createuser(driver);
		}
		
		@Test(priority = 6)
		public void frontendLogIn() throws Exception {
			driver.get(Constant.URL_front);
			frontEnd.frontLogin(driver);
		}
		
		@Test(priority = 7)
		public void formFillnagative() throws Exception {
			frontEnd.formFillnagative(driver);
		}
		
		@Test(priority = 8)
		public void formFill() throws Exception {
			frontEnd.formFill(driver);
		}
		
/*
		@Test(priority = 4)
		private void frontendCreateEvent() throws InterruptedException {
			Admin.frontendCreateEvent(driver);
			System.out.println("Event Created Successfully...!!! :-)");
		}
			@Test(priority = 5)
			private void frontendBooking() throws InterruptedException {
				Admin.Booking(driver);
				
		}
*/	}
}