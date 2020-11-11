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
	
	@DataProvider(name = "singleselectfieldcreation")

	public static Object[][] singleselectfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "singleselect");

	}
	
	@DataProvider(name = "multiselectfieldcreation")

	public static Object[][] multiselectfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "multiselect");

	}	
	
	@DataProvider(name = "textareafieldcreation")

	public static Object[][] textareafieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "textarea");

	}		
	@DataProvider(name = "editorfieldcreation")

	public static Object[][] editorfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "editor");

	}	
	
	@DataProvider(name = "filefieldcreation")

	public static Object[][] filefieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "file");

	}
	
	@DataProvider(name = "tcounterfieldcreation")

	public static Object[][] tcounterfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "textcounter");

	}
	
	@DataProvider(name = "checkboxfieldcreation")

	public static Object[][] checkboxfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "checkbox");

	}
	@DataProvider(name = "sqlfieldcreation")

	public static Object[][] sqlfieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "sql");

	}
	@DataProvider(name = "ucmsubformfiledcreation")

	public static Object[][] ucmsubformfiledcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "ucmsubform");

	}
	@DataProvider(name = "videofieldcreation")

	public static Object[][] videofieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "video");

	}
	@DataProvider(name = "audiofieldcreation")

	public static Object[][] audiofieldcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "audio");

	}
	@DataProvider(name = "textcategorycreation")

	public static Object[][] textcategorycreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "category");

	}
	
	
	@DataProvider(name = "menuviewcreation")

	public static Object[][] menuviewcreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "menu");

	}
	
	@DataProvider(name = "usercreation")

	public static Object[][] usercreation() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "user");

	}
	@DataProvider(name = "frontlogin")

	public static Object[][] frontlogin() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "frontlogin");

	}
	
	@DataProvider(name = "frontpostiveflow")

	public static Object[][] frontpostiveflow() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "frontpositive");

	}
	@DataProvider(name = "advancesearch")

	public static Object[][] advancesearch() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "advancesearch");

	}	
	
	@DataProvider(name = "deleteucm")

	public static Object[][] deleteucm() throws IOException {

		return ExcelUtils.getExcelData(TESTDATAEXCELFILE, "deleteucm");

	}
	
}
