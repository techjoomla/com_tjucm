package com.tekdi.nfta.test;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
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
	private Method[] method;
	private Actions action;
	private String currentTestDataSheet;
	private Method takesScreenshot;

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
		method = action.getClass().getDeclaredMethods();
		try {
			takesScreenshot = action.getClass().getDeclaredMethod("takesScreenshot", String.class, String.class);
		} catch (NoSuchMethodException | SecurityException e) {
			logger.severe(e.getMessage());
		}
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
						try {
							ReportUtils.createReport();
						} catch (Exception e) {
							e.printStackTrace();
						}
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
	 * executeActions is for execution of Actions based on the criteria mentioned
	 * in the testData excel file
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

				for (int i = 0; i < method.length; i++) {
					if (method[i].getName().equals(currentKeyword)) {
						String executionResult = null;
						try {
							executionResult = (String) method[i].invoke(action, locator, testData);
						} catch (IllegalAccessException | IllegalArgumentException | InvocationTargetException e) {
							logger.severe(e.getMessage());
						}
						try {
							testStepResult.add(executionResult);
						} catch (ArrayIndexOutOfBoundsException e) {
							logger.severe(e.getMessage());
						}
						try {
							takesScreenshot.invoke(action, currentTestCase + "_" + java.time.LocalDate.now() + "_"
									+ currentTestStepID + "_" + (currentTestDataID - 1), executionResult);
						} catch (IllegalAccessException | IllegalArgumentException | InvocationTargetException e) {
							logger.severe(e.getMessage());
						}
					}
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
			testDataxls.addColumn(Constant.TEST_STEPS_SHEET.getValue(), colName);
		}

		int index = 0;

		for (int i = 2; i <= testDataxls.getRowCount(Constant.TEST_STEPS_SHEET.getValue()); i++) {

			if (currentTestCase.equals(testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(),
					Constant.TEST_CASE_ID.getValue(), i))) {

				try {
					if (testStepResult.isEmpty())
						testDataxls.setCellData(Constant.TEST_STEPS_SHEET.getValue(), colName, i,
								Constant.KEYWORD_SKIP.getValue());
					else
						testDataxls.setCellData(Constant.TEST_STEPS_SHEET.getValue(), colName, i,
								testStepResult.get(index));
					index++;
				} catch (ArrayIndexOutOfBoundsException e) {
					logger.severe(e.getMessage());
				}

			}
		}

		if (testStepResult.isEmpty()) {
			testDataxls.setCellData(currentTestCase, Constant.RESULT.getValue(), currentTestDataID,
					Constant.KEYWORD_SKIP.getValue());
		} else {

			for (int i = 0; i < testStepResult.size(); i++) {

				try {
					if (!testStepResult.get(i).equals(Constant.KEYWORD_PASS.getValue())) {
						testDataxls.setCellData(currentTestCase, Constant.RESULT.getValue(), currentTestDataID,
								testStepResult.get(i));
					}
				} catch (ArrayIndexOutOfBoundsException e) {
					logger.severe(e.getMessage());
				}

			}
		}
	}
}
