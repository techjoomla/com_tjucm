package com.ucm.utils;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;

import org.apache.poi.ss.usermodel.Cell;
import org.apache.poi.ss.usermodel.DataFormatter;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFRow;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;

/**
 * This is Page Class for Utils. It contains all the elements and actions
 * related to Utils.
 * 
 */

public class ExcelUtils {

	static XSSFWorkbook workbook;

	static XSSFSheet worksheet;

	static XSSFRow row;

	static XSSFCell cell;

	static DataFormatter formatter;

	static int rowNum;

	static int colNum;

	public static Object[][] getExcelData(String filePath, String sheetName) throws IOException {

		FileInputStream fis;

		fis = new FileInputStream(filePath);

		workbook = new XSSFWorkbook(fis);

		workbook.close();

		worksheet = workbook.getSheet(sheetName);

		row = worksheet.getRow(0);

		rowNum = worksheet.getPhysicalNumberOfRows();

		colNum = row.getLastCellNum();

		Object[][] excelData = new Object[rowNum - 1][colNum];

		for (int i = 0; i < rowNum - 1; i++) {

			row = worksheet.getRow(i + 1);

			for (int j = 0; j < colNum; j++) {

				cell = row.getCell(j);

				formatter = new DataFormatter();

				excelData[i][j] = formatter.formatCellValue(cell);
			}
		}

		return excelData;

	}

	public static void setExcelData(String result, String filePath, String sheetName, String colName) {

		worksheet = workbook.getSheet(sheetName);

		row = worksheet.getRow(0);

		Iterator<Cell> cellIterator = row.cellIterator();

		while (cellIterator.hasNext()) {

			Cell cell = cellIterator.next();

			if (cell.getStringCellValue().equals(colName)) {

				cell.setCellValue(result);

			} else {

				cell = row.createCell(colNum + 1);

				cell.setCellValue(result);
			}
		}

		FileOutputStream fos;

		try {
			fos = new FileOutputStream(filePath);

			workbook.write(fos);

			fos.close();

		} catch (Exception e) {

			e.printStackTrace();

		}

	}

	public static Object[][] dataSupplier(String fileName) throws IOException {

		File file = new File(fileName);
		FileInputStream fis = new FileInputStream(file);
		workbook = new XSSFWorkbook(fis);
		worksheet = workbook.getSheetAt(0);
		workbook.close();
		int lastRowNum = worksheet.getLastRowNum();
		int lastCellNum = worksheet.getRow(0).getLastCellNum();
		Object[][] obj = new Object[lastRowNum][1];

		for (int i = 0; i < lastRowNum; i++) {
			Map<Object, Object> datamap = new HashMap<>();
			for (int j = 0; j < lastCellNum; j++) {
				datamap.put(worksheet.getRow(0).getCell(j).toString(), worksheet.getRow(i + 1).getCell(j).toString());
			}
			obj[i][0] = datamap;

		}
		return obj;
	}

/*	public static void setExcelFile(String path, String sheetName) throws Exception {

		try {
			File file = new File(Constant.TESTDATAPATH);
			FileInputStream excelFile = new FileInputStream(file);
			workbook = new XSSFWorkbook(excelFile);
			worksheet = workbook.getSheet(sheetName);
		} catch (Exception e) {
			throw (e);
		}

	}

	public static String getCellData(int rowNum, int colNum) {

		try {
			cell = worksheet.getRow(rowNum).getCell(colNum);
			DataFormatter df = new DataFormatter();
			String value = df.formatCellValue(cell);
			return value;
		} catch (Exception e) {
			return "";
		}

	}
*/
}
