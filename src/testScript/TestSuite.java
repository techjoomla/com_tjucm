package testScript;

import org.testng.annotations.Test;

import Excel.Constant;
import Action.Admin;
import config.BasicClass;

public class TestSuite {

	public class Module_TC01 extends BasicClass {

		@Test(priority = 1)
		public void AdminLogIn() throws Exception {
			driver.get(Constant.URL_admin);
			Admin.Login(driver);
		}

		@Test(priority = 2)
		private void Createtype() throws Exception {
			Admin.CreateType(driver);
			
		}
		
		@Test(priority = 3)
		private void CreateMenu()throws Exception{
			Admin.CreateMenu(driver);
		}
		
		@Test(priority = 4)
		private void Createuser()throws Exception {
			Admin.Createuser(driver);
		}
	/*	
		@Test(priority = 3)
		public void UserLogIn() throws Exception {
			driver.get(Constant.URL1);
			Admin.Login1(driver);
		}

		@Test(priority = 4)
		private void frontendCreateEvent() throws InterruptedException {
			Admin.frontendCreateEvent(driver);
			System.out.println("Event Created Successfully...!!! :-)");
		}
			@Test(priority = 5)
			private void frontendBooking() throws InterruptedException {
				Admin.Booking(driver);
				
		}*/
	}
}