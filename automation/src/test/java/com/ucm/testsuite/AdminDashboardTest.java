package com.ucm.testsuite;

import org.testng.annotations.Test;

import com.ucm.config.BaseClass;
import com.ucm.pageobjects.AdminDashboardPage;

public class AdminDashboardTest extends BaseClass {

	@Test
	public void admindashboard() {

		AdminDashboardPage dashboardpage = new AdminDashboardPage(driver);
		driver.get(properties.getProperty("url") + properties.getProperty("admin"));
		logger = extent.createTest(new Object() {
		}.getClass().getEnclosingMethod().getName());
		dashboardpage.dashboard();

	}

}
