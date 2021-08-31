package com.tekdi.nfta.utils;

import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.logging.Logger;

import org.apache.poi.ss.usermodel.DataFormatter;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFRow;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;

import com.tekdi.nfta.config.Constant;

public class ExcelUtils {

	private XSSFWorkbook workbook;
	private XSSFSheet worksheet;
	private XSSFRow row;
	private XSSFCell cell;
	private DataFormatter formatter;
	private FileInputStream fis;
	private String path;
	private FileOutputStream fos;

	Logger logger = Logger.getLogger(ExcelUtils.class.getName());

	public ExcelUtils(String path) {
		this.path = path;
		try {
			fis = new FileInputStream(path);
			workbook = new XSSFWorkbook(fis);
			worksheet = workbook.getSheetAt(0);
			fis.close();
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
		} catch (IOException e) {
			logger.severe(e.getMessage());
		}
	}

	public int getRowCount(String sheetName) {
		int index = 0;
		try {
			index = workbook.getSheetIndex(sheetName);
		} catch (Exception e) {
			logger.warning(sheetName + Constant.ERROR_FINDINGSHEET.getValue());
		}
		if (index == -1)
			return 0;
		else {
			try {
				worksheet = workbook.getSheetAt(index);
				row = worksheet.getRow(1);
				cell = row.getCell(1);
				return worksheet.getLastRowNum() + 1;
			} catch (Exception e) {
				logger.warning(sheetName + Constant.ERROR_FINDINGSHEET.getValue());
				return 0;
			}
		}
	}

	public String getCellData(String sheetname, String colname, int rownum) {
		try {
			if (rownum <= 0) {
				return "";
			}
			int index = workbook.getSheetIndex(sheetname);
			int colNum = -1;
			if (index == -1) {
				return "";
			}
			worksheet = workbook.getSheetAt(index);
			row = worksheet.getRow(0);
			for (int i = 0; i < row.getLastCellNum(); i++) {
				if (row.getCell(i).getStringCellValue().equals(colname)) {
					colNum = i;
				}
			}
			if (colNum == -1) {
				logger.warning(colname + " column doesn't exist in the sheet " + sheetname);
				return "";
			}
			row = worksheet.getRow(rownum - 1);
			if (row == null) {
				return "";
			}
			cell = row.getCell(colNum);
			if (cell == null) {
				return "";
			}

			switch (cell.getCellTypeEnum()) {
			case STRING:
				return cell.getStringCellValue();
			case NUMERIC:
				formatter = new DataFormatter();
				return formatter.formatCellValue(cell);
			case FORMULA:
				return formatter.formatCellValue(cell);
			case BLANK:
				return "";
			default:
				return formatter.formatCellValue(cell);
			}
		} catch (Exception e) {
			logger.severe(e.getMessage() + "row " + rownum + " or column " + colname + " does not exist in xls");
			return "row " + rownum + " or column " + colname + " does not exist in xls";
		}
	}

	public String getCellData(String sheetname, int colNum, int rownum) {

		worksheet = workbook.getSheet(sheetname);

		row = worksheet.getRow(0);

		if (colNum == 0 || rownum <= 0) {
			return "";
		}
		row = worksheet.getRow(rownum - 1);
		cell = row.getCell(colNum);
		
		if(cell==null) {
			return "";
		}
		
		switch (cell.getCellTypeEnum()) {
		case STRING:
			return cell.getStringCellValue();
		case NUMERIC:
			formatter = new DataFormatter();
			return formatter.formatCellValue(cell);
		case FORMULA:
			return formatter.formatCellValue(cell);
		case BLANK:
			return "";
		default:
			return formatter.formatCellValue(cell);
		}

	}

	public boolean setCellData(String sheetname, String colName, int rowNum, String data) {

		try {
			fis = new FileInputStream(path);
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			workbook = new XSSFWorkbook(fis);
		} catch (IOException e) {
			logger.severe(e.getMessage());
			return false;
		}
		if (rowNum <= 0)
			return false;
		int index = workbook.getSheetIndex(sheetname);
		int colNum = -1;
		if (index == -1)
			return false;
		worksheet = workbook.getSheetAt(index);
		row = worksheet.getRow(0);
		for (int i = 0; i < row.getLastCellNum(); i++) {
			if (row.getCell(i).getStringCellValue().trim().equals(colName))
				colNum = i;
		}
		if (colNum == -1)
			return false;
		worksheet.autoSizeColumn(colNum);
		row = worksheet.getRow(rowNum - 1);
		if (row == null)
			row = worksheet.createRow(rowNum - 1);
		cell = row.getCell(colNum);
		if (cell == null)
			cell = row.createCell(colNum);
		cell.setCellValue(data);
		try {
			fos = new FileOutputStream(path);
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			workbook.write(fos);
		} catch (IOException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			fos.close();
		} catch (IOException e) {
			logger.severe(e.getMessage());
			return false;
		}

		return true;

	}

	public boolean isSheetExist(String sheetname) {
		int index = workbook.getSheetIndex(sheetname);

		if (index == -1) {
			logger.severe(sheetname + Constant.ERROR_FINDINGDATASHEET.getValue());
			return false;
		}
		return true;

	}

	public int getColumnCount(String sheetName) {
		if (!isSheetExist(sheetName)) {
			logger.warning(sheetName + Constant.ERROR_FINDINGSHEET.getValue());
			return -1;
		}
		worksheet = workbook.getSheet(sheetName);
		row = worksheet.getRow(0);
		if (row == null) {
			return -1;
		}
		return row.getLastCellNum();
	}

	public boolean addColumn(String sheetName, String colName) {

		try {
			fis = new FileInputStream(path);
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			workbook = new XSSFWorkbook(fis);
		} catch (IOException e) {
			logger.severe(e.getMessage());
			return false;
		}
		int index = workbook.getSheetIndex(sheetName);
		if (index == -1) {
			logger.severe(sheetName + Constant.ERROR_FINDINGSHEET.getValue());
			return false;
		}
		worksheet = workbook.getSheetAt(index);
		row = worksheet.getRow(0);
		if (row == null)
			row = worksheet.createRow(0);
		if (row.getLastCellNum() == -1)
			cell = row.createCell(0);
		else
			cell = row.createCell(row.getLastCellNum());
		cell.setCellValue(colName);
		try {
			fos = new FileOutputStream(path);
		} catch (FileNotFoundException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			workbook.write(fos);
		} catch (IOException e) {
			logger.severe(e.getMessage());
			return false;
		}
		try {
			fos.close();
		} catch (IOException e) {
			e.printStackTrace();
			return false;
		}

		return true;
	}
}
