package com.tekdi.nfta.utils;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileWriter;
import java.io.IOException;
import java.util.Date;
import java.util.Properties;
import java.util.logging.Logger;

import com.tekdi.nfta.config.Constant;

public class ReportUtils {

	public static String result_FolderName = null;

	static Logger logger = Logger.getLogger(ReportUtils.class.getName());

	public static void createReport() {

		Date d = new Date();
		result_FolderName = "Reports";
		new File(result_FolderName).mkdirs();

		FileInputStream fs;
		String environment = null;
		String release = null;
		try {
			fs = new FileInputStream(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
					+ Constant.NFTA_RESOURCES_PATH.getValue() + Constant.OBJECT_REPOSITORY_DATA.getValue());
			Properties CONFIG = new Properties();
			CONFIG.load(fs);
			environment = CONFIG.getProperty(Constant.ENVIRONMENT.getValue());
			release = CONFIG.getProperty(Constant.RELEASE.getValue());
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
		} catch (IOException e) {
			logger.severe(e.getMessage());
		}

		ExcelUtils testDataxls = new ExcelUtils(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
				+ Constant.TEST_RESOURCES_PATH.getValue() + Constant.TEST_DATA_EXCEL.getValue());

		// create index.html
		String indexHtmlPath = result_FolderName + "/" + Constant.REPORT_FILE_NAME.getValue();
		try {
			new File(indexHtmlPath).createNewFile();
		} catch (IOException e) {
			logger.severe(e.getMessage());
		}

		try {
			FileWriter fileWriter = new FileWriter(indexHtmlPath);
			BufferedWriter out = new BufferedWriter(fileWriter);
			out.write(
					"<html><HEAD> <TITLE>Automation Test Results</TITLE></HEAD><body><h4 align=center><FONT COLOR=660066 FACE=AriaL SIZE=6><b><u> Automation Test Results</u></b></h4><table  border=1 cellspacing=1 cellpadding=1 ><tr><h4> <FONT COLOR=660000 FACE=Arial SIZE=4.5> <u>Test Details :</u></h4><td width=150 align=left bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE=Arial SIZE=2.75><b>Run Date</b></td><td width=150 align=left><FONT COLOR=#153E7E FACE=Arial SIZE=2.75><b>");
			out.write(d.toString());
			out.write(
					"</b></td></tr><tr><td width=150 align=left bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE=Arial SIZE=2.75><b>Run Environment</b></td><td width=150 align=left><FONT COLOR=#153E7E FACE=Arial SIZE=2.75><b>");
			out.write(environment);
			out.write(
					"</b></td></tr><tr><td width=150 align= left  bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE= Arial  SIZE=2.75><b>Release</b></td><td width=150 align= left ><FONT COLOR=#153E7E FACE= Arial  SIZE=2.75><b>");
			out.write(release);
			out.write(
					"</b></td></tr></table><h4> <FONT COLOR=660000 FACE= Arial  SIZE=4.5> <u>Report :</u></h4><table  border=1 cellspacing=1 cellpadding=1 width=100%><tr><td width=20% align= center  bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE= Arial  SIZE=2><b>TEST CASE NAME</b></td><td width=40% align= center  bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE= Arial  SIZE=2><b>DESCRIPTION</b></td><td width=10% align= center  bgcolor=#153E7E><FONT COLOR=#E0E0E0 FACE= Arial  SIZE=2><b>EXECUTION RESULT</b></td></tr>");

			int testcasecount = testDataxls.getRowCount(Constant.TEST_CASES_SHEET.getValue());
			String currentTestCase = null;
			String result = " ";
			for (int currentTestCaseID = 2; currentTestCaseID <= testcasecount; currentTestCaseID++) {

				currentTestCase = testDataxls.getCellData(Constant.TEST_CASES_SHEET.getValue(),
						Constant.TEST_CASE_ID.getValue(), currentTestCaseID);

				for (int currentStepID = 2; currentStepID <= testDataxls
						.getRowCount(Constant.TEST_STEPS_SHEET.getValue()); currentStepID++) {
					// make the file corresponding to test Steps
					String testSteps_file = result_FolderName + "//" + currentTestCase + "_steps.html";
					new File(testSteps_file).createNewFile();
					if (currentTestCase.equals(testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(),
							Constant.TEST_CASE_ID.getValue(), currentStepID))) {
						result = "";

						int cols = testDataxls.getColumnCount(Constant.TEST_STEPS_SHEET.getValue());
						int rows = testDataxls.getRowCount(Constant.TEST_STEPS_SHEET.getValue());
						FileWriter fw_test_steps = new FileWriter(testSteps_file);
						BufferedWriter out_test_steps = new BufferedWriter(fw_test_steps);
						out_test_steps.write("<html><HEAD> <TITLE>" + currentTestCase
								+ " Test Results</TITLE></HEAD><body><h4 align=center><FONT COLOR=660066 FACE=AriaL SIZE=6><b><u> "
								+ currentTestCase
								+ " Detailed Test Results</u></b></h4><table width=100% border=1 cellspacing=1 cellpadding=1 >");
						out_test_steps.write("<tr>");

						for (int colNum = 0; colNum < cols; colNum++) {
							out_test_steps.write(
									"<td align= center bgcolor=#153E7E><FONT COLOR=#ffffff FACE= Arial  SIZE=2><b>");
							out_test_steps
									.write(testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(), colNum, 1));
						}
						out_test_steps.write("</b></tr>");

						boolean result_col = false;
						for (int rowNum = 2; rowNum <= rows; rowNum++) {
							out_test_steps.write("<tr>");
							for (int colNum = 0; colNum < cols; colNum++) {

								if (testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(),
										Constant.TEST_CASE_ID.getValue(), rowNum).equals(currentTestCase)) {

									String data = testDataxls.getCellData(Constant.TEST_STEPS_SHEET.getValue(), colNum,
											rowNum);

									result_col = testDataxls
											.getCellData(Constant.TEST_STEPS_SHEET.getValue(), colNum, 1)
											.startsWith(Constant.RESULT.getValue());
									if (data.isEmpty()) {
										if (result_col)
											data = "SKIP";
										else
											data = " ";
									}
									if ((data.startsWith("Pass") || data.startsWith("PASS")) && result_col) {
										out_test_steps.write(
												"<td align=center bgcolor=green><FONT COLOR=#000000 FACE= Arial  SIZE=1>");
									} else if ((data.startsWith("Fail") || data.startsWith("FAIL")) && result_col) {
										out_test_steps.write(
												"<td align=center bgcolor=red><FONT COLOR=#000000 FACE= Arial  SIZE=1>");
										result = "FAIL";
									} else
										out_test_steps.write(
												"<td align= center bgcolor=#ffffff><FONT COLOR=#000000 FACE= Arial  SIZE=1>");
									out_test_steps.write(data);
								}
							}
							out_test_steps.write("</tr>");
						}

						out_test_steps.write("<tr>");
						out_test_steps.write("</table>");
						out_test_steps.close();
					}
				}
				out.write("<tr><td width=20% align= center><FONT COLOR=#153E7E FACE= Arial  SIZE=2><b>");
				out.write("<a href=" + currentTestCase.replace(" ", "%20") + "_steps.html>" + currentTestCase + "</a>");
				out.write("</b></td><td width=40% align= center><FONT COLOR=#153E7E FACE= Arial  SIZE=2><b>");
				out.write(testDataxls.getCellData(Constant.TEST_CASES_SHEET.getValue(), Constant.DESCRIPTION.getValue(),
						currentTestCaseID));
				out.write("</b></td><td width=10% align=center  bgcolor=");
				if (testDataxls.getCellData(Constant.TEST_CASES_SHEET.getValue(), Constant.RUNMODE.getValue(),
						currentTestCaseID).equalsIgnoreCase(Constant.RUNMODE_YES.getValue())) {
					if (result.equalsIgnoreCase("FAIL"))
						out.write("red><FONT COLOR=153E7E FACE=Arial SIZE=2><b>FAIL</b></td></tr>");

					else
						out.write("green><FONT COLOR=153E7E FACE=Arial SIZE=2><b>PASS</b></td></tr>");
				} else
					out.write("yellow><FONT COLOR=153E7E FACE=Arial SIZE=2><b>SKIP</b></td></tr>");
			}
			out.write("</table>");
			out.close();
		} catch (IOException e) {
			logger.severe(e.getMessage());
		}
	}
}