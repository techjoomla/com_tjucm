package Excel;

import java.io.File;
import java.io.FileInputStream;

import org.apache.poi.ss.usermodel.DataFormatter;
import org.apache.poi.xssf.usermodel.XSSFCell;
import org.apache.poi.xssf.usermodel.XSSFSheet;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;

import Excel.Constant;

public class ExcellPath {

	private static XSSFSheet ExcelWsheet;

	private static XSSFWorkbook ExcelWbook;

	private static XSSFCell cell;
	
	public static void setExcelFile(String Path, String SheetName) throws Exception {

		try {
			File file = new File(Constant.TestData_Path);

			FileInputStream ExcelFile = new FileInputStream(file);

			ExcelWbook = new XSSFWorkbook(ExcelFile);

			ExcelWsheet = ExcelWbook.getSheet("Sheet1");

		} catch (Exception e) {

			throw (e);
		}

	}

	public static String getCellData(int RowNum, int ColNum) {

		try {

			cell = ExcelWsheet.getRow(RowNum).getCell(ColNum);
			DataFormatter df = new DataFormatter();
			String value = df.formatCellValue(cell);
			String CellData = cell.getStringCellValue();
			return CellData;

		} catch (Exception e) {

			return "";

		}

	}

}
