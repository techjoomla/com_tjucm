package com.tekdi.nfta.test;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Properties;
import java.util.logging.Logger;
import org.testng.annotations.Test;
import com.tekdi.nfta.config.Constant;
import com.tekdi.nfta.utils.ExcelUtils;
import com.tekdi.nfta.utils.ReportUtils;

public class NFTADriver {

	private ExcelUtils testDataxls;
	static FileInputStream fis;
	protected static Properties ObjectRepository;
	private String currentTestCase;
	private List<String> testStepResult;
	private int currentTestDataID = 2;
	protected static String testData;
	protected static String locator;
	private Actions action;
	private String currentTestDataSheet;
	protected static int stepcount=2;
	static Logger logger = Logger.getLogger(NFTADriver.class.getName());

	/**
	 * This is Driver constructor. It initializes Keyword class and get all its
	 * declared methods
	 * 
	 * @throws SecurityException
	 * @throws NoSuchMethodException
	 * 
	 */

	public NFTADriver() {
		action = new Actions();
	}

	/**
	 * 
	 * This is the entry method which gets executed. It locates the object
	 * repository file and initializes it.
	 * 
	 * @throws IOException
	 * @throws IllegalAccessException
	 * @throws IllegalArgumentException
	 * @throws InvocationTargetException
	 * @throws SecurityException
	 * @throws NoSuchMethodException
	 */

	@Test
	public static void main() {

		try {
			fis = new FileInputStream(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
					+ Constant.NFTA_RESOURCES_PATH.getValue() + "objectrepository.properties");
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
		}
		ObjectRepository = new Properties();
		try {
			ObjectRepository.load(fis);
		} catch (IOException e) {
			logger.severe(e.getMessage());
		}
		NFTADriver driver = new NFTADriver();
		driver.start();

	}

	/**
	 * Start method locates the TestData file and takes all the necessary
	 * information as per the provided Runmode in Test Data file and executes it.
	 * 
	 * @throws IllegalAccessException
	 * @throws IllegalArgumentException
	 * @throws InvocationTargetException
	 */

	public void start() {

		logger.info(
				"PLEASE MAKE SURE TESTDATA EXCEL FILE IS SAVED AND CLOSED PROPERLY, TO PREVENT ANY DATA LOSS OR FILE CORRUPTION");
		testDataxls = new ExcelUtils(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
				+ Constant.TEST_RESOURCES_PATH.getValue() + "TestData.xlsx");

		//Loop starts from Second Row to Skip the header row
		
		for (int currentTestCaseID = 2; currentTestCaseID <= testDataxls
				.getRowCount(Constant.TEST_CASES_SHEET.getValue()); currentTestCaseID++) {

			if (testDataxls
					.getCellData(Constant.TEST_CASES_SHEET.getValue(), Constant.RUNMODE.getValue(), currentTestCaseID)
					.equals(Constant.RUNMODE_YES.getValue())) {

				currentTestCase = testDataxls.getCellData(Constant.TEST_CASES_SHEET.getValue(),
						Constant.TEST_CASE_ID.getValue(), currentTestCaseID);

				currentTestDataSheet = currentTestCase + Constant.TEST_DATA.getValue();

				if (testDataxls.isSheetExist(currentTestDataSheet)) {
					for (currentTestDataID = 2; currentTestDataID <= testDataxls
							.getRowCount(currentTestDataSheet); currentTestDataID++) {
						testStepResult = new ArrayList<>();
						if (testDataxls
								.getCellData(currentTestDataSheet, Constant.RUNMODE.getValue(), currentTestDataID)
								.equals(Constant.RUNMODE_YES.getValue())) {
			 				try {
								executeActions();
							} catch (IllegalArgumentException e) {
								logger.severe(e.getMessage());
							}
						} else if (testDataxls
								.getCellData(currentTestDataSheet, Constant.RUNMODE.getValue(), currentTestDataID)
								.isEmpty()) {
							logger.warning(Constant.NO_TESTDATA_WITH_RUNMODE_YES.getValue() + currentTestDataSheet);
						}
						testStepResult();
						
						 try { ReportUtils.createReport(); } catch (Exception e) {
						 e.printStackTrace(); }
						 
					}

				}
			} else if (testDataxls
					.getCellData(Constant.TEST_CASES_SHEET.getValue(), Constant.RUNMODE.getValue(), currentTestCaseID)
					.isEmpty()) {
				logger.warning(Constant.NO_TESTCASE_WITH_RUNMODE_YES.getValue() + Constant.TEST_CASES_SHEET.getValue());
			}
		}

	}

	/**
	 * 
	 * executeActions is for execution of Actions based on the criteria mentioned in
	 * the testData excel file
	 * 
	 * @throws IllegalAccessException
	 * @throws IllegalArgumentException
	 * @throws InvocationTargetException
	 */

	private void executeActions() {

		for (int currentTestStepID = 2; currentTestStepID <= testDataxls
				.getRowCount(Constant.TEST_STEPS_SHEET.getValue()); currentTestStepID++) {

			if (currentTestDataSheet.contains(testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(),
					Constant.TEST_CASE_ID.getValue(), currentTestStepID))) {

				testData = testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(), Constant.DATA.getValue(),
						currentTestStepID);

				if (testData.startsWith(Constant.START_COL.getValue())) {

					testData = testDataxls.getCellData(currentTestDataSheet,
							testData.split(Constant.DATA_SPLIT.getValue())[1], currentTestDataID);
				} else if (testData.startsWith(Constant.OBJECTREPOSITORY.getValue())) {
					testData = ObjectRepository.getProperty(testData.split(Constant.DATA_SPLIT.getValue())[1]);
				}
				locator = testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(), Constant.LOCATOR.getValue(),
						currentTestStepID);
				String currentKeyword = testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(),
						Constant.ACTION.getValue(), currentTestStepID);
				
				 switch(currentKeyword) {
				 
				 case "openBrowser": 
					 
					 String executionResult = null;
					 executionResult = (String) action.openBrowser(locator, testData);
					 testStepResult.add(executionResult);
					 break;
					 
				 case "navigateTo": 
					 
					 executionResult = (String) action.navigateTo(locator, testData);
					 testStepResult.add(executionResult);
					 break;
					 
				 case "enterTextByID": 
		
					 executionResult = (String) action.enterTextByID(locator, testData);
					 testStepResult.add(executionResult);
					 break; 
					 
				 case "enterTextByName": 
					 
					 executionResult = (String) action.enterTextByName(locator, testData);
					 testStepResult.add(executionResult);
					 break; 
					 
				 case "enterTextByXpath": 
					 
					 executionResult = (String) action.enterTextByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;		 
					 
				 case "enterTextByCss": 
					 
					 executionResult = (String) action.enterTextByCss(locator, testData);
					 testStepResult.add(executionResult);
					 break;
					 
				 case "enterTextByClassName": 
					 
					 executionResult = (String) action.enterTextByClassName(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickElementByID": 
					 
					 executionResult = (String) action.clickElementByID(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "clickElementByName": 
					 
					 executionResult = (String) action.clickElementByName(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickElementByXpath": 
					 
					 executionResult = (String) action.clickElementByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickElementByCss": 
					 
					 executionResult = (String) action.clickElementByCss(locator, testData);
					 testStepResult.add(executionResult);
					 break;
					 
				 case "acceptAlert": 
					 
					 executionResult = (String) action.acceptAlert(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "dismissAlert": 
					 
					 executionResult = (String) action.dismissAlert(locator, testData);
					 testStepResult.add(executionResult);
					 break;
					 
				 case "clickOnLinkByLinkTextByData": 
					 
					 executionResult = (String) action.clickOnLinkByLinkTextByData(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickOnLinkByLinkTextByLocator": 
					 
					 executionResult = (String) action.clickOnLinkByLinkTextByLocator(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickOnLinkByLinkText": 
					 
					 executionResult = (String) action.clickOnLinkByLinkText(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickOnLinkByPartialLinkText": 
					 
					 executionResult = (String) action.clickOnLinkByPartialLinkText(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "fileupload": 
					 
					 executionResult = (String) action.fileupload(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 	 
				 case "scorllDown": 
					 
					 executionResult = (String) action.scorllDown(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "scorllUp": 
					 
					 executionResult = (String) action.scorllUp(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "selectCheckBoxByCss": 
					 
					 executionResult = (String) action.selectCheckBoxByCss(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "selectClassDropdownByXpath": 
					 
					 executionResult = (String) action.selectClassDropdownByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;		 
					 
				 case "clickButtonByText": 
					 
					 executionResult = (String) action.clickButtonByText(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "clickDropdownByXpath": 
					 
					 executionResult = (String) action.clickDropdownByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "waitForPageLoad": 
					 
					 executionResult = (String) action.waitForPageLoad();
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "elementToBeClickableDropdown": 
					 
					 executionResult = (String) action.elementToBeClickableDropdown(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "verifyPopupMessage": 
					 
					 executionResult = (String) action.verifyPopupMessage(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "verifyPopupMessageByXpath": 
					 
					 executionResult = (String) action.verifyPopupMessageByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "pause": 
					 
					 executionResult = (String) action.pause(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickOnRadioByCss": 
					 
					 executionResult = (String) action.clickOnRadioByCss(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "clickOnRadioWithLabel": 
					 
					 executionResult = (String) action.clickOnRadioWithLabel(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "clickOnRadioWithValue": 
					 
					 executionResult = (String) action.clickOnRadioWithValue(locator, testData);
					 testStepResult.add(executionResult);
					 break;		
					 
				 case "VerifyAPICallStatusIs200": 
					 
					 executionResult = (String) action.VerifyAPICallStatusIs200(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "enterClearTextByXpath": 
					 
					 executionResult = (String) action.enterClearTextByXpath(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				 case "quitBrowser": 
					 
					 executionResult = (String) action.quitBrowser(locator, testData);
					 testStepResult.add(executionResult);
					 break;	
					 
				default :
					break;	

				 }
			}
		}
	}

	/**
	 * 
	 * testStepResult updates the test step result after the action execution in the
	 * testdata file.
	 * 
	 */

	private void testStepResult() {

		String colName = Constant.RESULT.getValue() + (currentTestDataID - 1);

		boolean isColExist = false;

		for (int c = 0; c < testDataxls.getColumnCount(Constant.TEST_STEPS_SHEET.getValue()); c++) {
			if (testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(), c, 1).equals(colName)) {
				isColExist = true;
				break;
			}
		}

		
		 if (!isColExist) {
		 testDataxls.addColumn(Constant.TEST_STEPS_SHEET.getValue(), colName); }
		 

		int index = 0;

		for (int i = 2; i <= testDataxls.getRowCount(Constant.TEST_STEPS_SHEET.getValue()); i++) {

			
			 if (currentTestCase.equals(testDataxls.getCellData(Constant.TEST_STEPS_SHEET.
			 getValue(), Constant.TEST_CASE_ID.getValue(), i))) {
			  
			 if (testStepResult.isEmpty())
			 testDataxls.setCellData(Constant.TEST_STEPS_SHEET.getValue(), colName, i,
			 Constant.KEYWORD_SKIP.getValue()); else { if (index < testStepResult.size())
			 { testDataxls.setCellData(Constant.TEST_STEPS_SHEET.getValue(), colName, i,
			 testStepResult.get(index)); }
			  
			 } index++; }
			 
		}

		
		  if (testStepResult.isEmpty()) { testDataxls.setCellData(currentTestCase,
		  Constant.RESULT.getValue(), currentTestDataID,
		  Constant.KEYWORD_SKIP.getValue()); } else {
		  
		  for (int i = 0; i < testStepResult.size(); i++) {
		 
		  try { if (!testStepResult.get(i).equals(Constant.KEYWORD_PASS.getValue())) {
		  testDataxls.setCellData(currentTestCase, Constant.RESULT.getValue(),
		  currentTestDataID, testStepResult.get(i)); } } catch
		  (ArrayIndexOutOfBoundsException e) { logger.severe(e.getMessage()); }
		  
		  } }
		 
	}
}
