package com.ucm.dataproviders;

import java.io.IOException;

import org.testng.annotations.DataProvider;

import com.ucm.utils.ExcelUtils;

public class DataProviderClass {
	
	public static final String TESTDATAEXCELFILE = "Testdata.xlsx";
	
	@DataProvider(name = "adminlogin")

	public static Object[][] adminlogin() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "ULoginDetails");

	}
	
	@DataProvider(name = "subformcreation")

	public static Object[][] subformcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "subform");

	}

	@DataProvider(name = "ucmformcreation")

	public static Object[][] ucmformcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "ucmForm");

	}
}
