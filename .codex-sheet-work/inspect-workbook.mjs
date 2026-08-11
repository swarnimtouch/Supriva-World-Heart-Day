import fs from "node:fs/promises";
import { FileBlob, SpreadsheetFile } from "@oai/artifact-tool";

const inputPath = "C:/Users/Swarnim-12/Downloads/World Heart Day.xlsx";
const outputDir = "C:/Users/Swarnim-12/.codex/visualizations/2026/08/11/019ff03f-430f-70d0-95ab-4ab89ad2062a";
const input = await FileBlob.load(inputPath);
const workbook = await SpreadsheetFile.importXlsx(input);

const summary = await workbook.inspect({
  kind: "workbook,sheet,table",
  maxChars: 12000,
  tableMaxRows: 12,
  tableMaxCols: 30,
  tableMaxCellChars: 120,
});
console.log(summary.ndjson);

const sheets = await workbook.inspect({ kind: "sheet", include: "id,name", maxChars: 4000 });
console.log(sheets.ndjson);

for (const sheet of workbook.worksheets.items) {
  const used = sheet.getUsedRange();
  const preview = await workbook.render({
    sheetName: sheet.name,
    range: "A1:G40",
    scale: 1,
    format: "png",
  });
  const safeName = sheet.name.replace(/[^a-z0-9_-]+/gi, "_");
  await fs.writeFile(`${outputDir}/${safeName}.png`, new Uint8Array(await preview.arrayBuffer()));
  console.log(JSON.stringify({ sheet: sheet.name, usedRange: used?.address ?? null, preview: `${outputDir}/${safeName}.png` }));
}
