package com.tekdi.nfta.config;

public enum Constant {

	TEST_CASES_SHEET("Test Cases"), 
	RUNMODE("Runmode"), 
	RUNMODE_YES("Y"), 
	TEST_CASE_ID("TCID"),
	TEST_STEPS_SHEET("Test Steps"), 
	TEST_DATA("_Test_Data"), 
	ACTION("Action"), 
	DATA("Data"), 
	START_COL("col"),
	DATA_SPLIT("\\|"), 
	OBJECTREPOSITORY("objectrepository"), 
	LOCATOR("Locator"), 
	RESULT("Result"), 
	KEYWORD_SKIP("Skip"), 
	KEYWORD_PASS("Pass"),
	KEYWORD_FAIL("Fail"), 
	PROJECT_ROOT_DIRECTORY("user.dir"), 
	TEST_RESOURCES_PATH("/nftaresources/testresources/"),
	NFTA_RESOURCES_PATH("/nftaresources/"), 
	TEST_CASE_RESULT("Result"),
	TEST_DATA_EXCEL("TestData.xlsx"),
	OBJECT_REPOSITORY_DATA("objectrepository.properties"),
	ENVIRONMENT("environment"),
	RELEASE("release"),
	REPORT_FILE_NAME("Test_Case_Report.html"),
	ERROR_SCREENSHOT("Unable to takescreenhot. Seems the reference for WebDriver is not instantiated. Value of driver is "),
	ERROR_FINDINGDATASHEET(" sheet doesn't exist or sheetname is not as per the given format. i.e {TCID}_Test_Data"),
	ERROR_FINDINGSHEET(" sheet doesn't exist or unable to find sheet with given name"),
	NO_TESTDATA_WITH_RUNMODE_YES("None of the Test Data has Runmode set to 'Y' in sheet >> "),
	NO_TESTCASE_WITH_RUNMODE_YES("None of the Test Cases has Runmode set to 'Y' in sheet >> "), 
	DESCRIPTION("Description"), 
	DETAILEDREPORT("Test Steps Detailed Report");


	private String constants;

	public String getValue() {
		return this.constants;
	}

	private Constant(String constant) {
		this.constants = constant;
	}

}
