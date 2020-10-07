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
	
	@DataProvider(name = "radiobuttoncreation")

	public static Object[][] radiobuttoncreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "radiobutton");

	}
	
	@DataProvider(name = "textfieldcreation")

	public static Object[][] textfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "textfield");

	}
	
	@DataProvider(name = "numberfieldcreation")

	public static Object[][] numberfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "numberfield");

	}
	
	@DataProvider(name = "datefieldcreation")

	public static Object[][] datefieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "datefield");

	}
		
	@DataProvider(name = "emailfieldcreation")

	public static Object[][] emailfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "emailfield");

	}
	
	
	
}
