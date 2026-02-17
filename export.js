/**
 * MICROFINANCIAL MANAGEMENT SYSTEM I
 * Shared Export Utilities — PDF & CSV
 * 
 * Dependencies:
 *   - jsPDF          (loaded via CDN)
 *   - jsPDF-AutoTable (loaded via CDN)
 *   - html2canvas     (loaded via CDN, for chart captures)
 */

const ExportHelper = (() => {

  // ─── CSV Export ───
  function exportCSV(filename, headers, rows) {
    const escape = v => {
      const s = String(v ?? '').replace(/"/g, '""');
      return s.includes(',') || s.includes('"') || s.includes('\n') ? `"${s}"` : s;
    };
    const lines = [headers.map(escape).join(',')];
    rows.forEach(r => lines.push(r.map(escape).join(',')));
    const blob = new Blob(['\uFEFF' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
    downloadBlob(blob, filename.endsWith('.csv') ? filename : filename + '.csv');
  }

  // ─── PDF Export (tables only) ───
  function exportPDF(filename, title, headers, rows, options = {}) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: options.landscape ? 'landscape' : 'portrait', unit: 'mm', format: 'a4' });

    addHeader(doc, title, options.subtitle || '');

    doc.autoTable({
      head: [headers],
      body: rows,
      startY: 32,
      styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak', lineColor: [229, 231, 235] },
      headStyles: { fillColor: [5, 150, 105], textColor: 255, fontStyle: 'bold', fontSize: 8 },
      alternateRowStyles: { fillColor: [249, 250, 251] },
      margin: { left: 12, right: 12 },
      didDrawPage: (data) => {
        addFooter(doc);
      }
    });

    doc.save(filename.endsWith('.pdf') ? filename : filename + '.pdf');
  }

  // ─── PDF Export with Charts (dashboard) ───
  async function exportDashboardPDF(filename, title, sections) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'mm', format: 'a4' });
    const pageW = doc.internal.pageSize.getWidth();
    let y = 10;

    addHeader(doc, title, 'Generated on ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
    y = 34;

    for (const section of sections) {
      if (section.type === 'stats') {
        // Stats summary row
        doc.setFontSize(11);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text(section.title || 'Overview', 12, y);
        y += 6;

        const statsPerRow = 4;
        const colW = (pageW - 24) / statsPerRow;
        section.items.forEach((item, i) => {
          const col = i % statsPerRow;
          const x = 12 + col * colW;
          if (i > 0 && col === 0) y += 14;

          doc.setFillColor(249, 250, 251);
          doc.roundedRect(x, y - 4, colW - 4, 12, 2, 2, 'F');
          doc.setFontSize(12);
          doc.setFont(undefined, 'bold');
          doc.setTextColor(5, 150, 105);
          doc.text(String(item.value), x + 4, y + 3);
          doc.setFontSize(7);
          doc.setFont(undefined, 'normal');
          doc.setTextColor(107, 114, 128);
          doc.text(item.label, x + 4, y + 7);
        });
        y += 18;
      }

      if (section.type === 'chart') {
        // Capture chart canvas as image
        const canvas = document.getElementById(section.canvasId);
        if (canvas) {
          if (y + 70 > doc.internal.pageSize.getHeight() - 20) {
            doc.addPage(); y = 14;
          }
          doc.setFontSize(10);
          doc.setFont(undefined, 'bold');
          doc.setTextColor(31, 41, 55);
          doc.text(section.title || '', 12, y);
          y += 4;

          try {
            const imgData = canvas.toDataURL('image/png');
            const ratio = canvas.width / canvas.height;
            const imgW = pageW - 24;
            const imgH = imgW / ratio;
            doc.addImage(imgData, 'PNG', 12, y, imgW, Math.min(imgH, 65));
            y += Math.min(imgH, 65) + 8;
          } catch(e) { console.warn('Chart capture failed:', e); y += 4; }
        }
      }

      if (section.type === 'table') {
        if (y + 30 > doc.internal.pageSize.getHeight() - 20) {
          doc.addPage(); y = 14;
        }
        doc.setFontSize(10);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(31, 41, 55);
        doc.text(section.title || '', 12, y);
        y += 2;

        doc.autoTable({
          head: [section.headers],
          body: section.rows,
          startY: y,
          styles: { fontSize: 7, cellPadding: 2, overflow: 'linebreak', lineColor: [229, 231, 235] },
          headStyles: { fillColor: [5, 150, 105], textColor: 255, fontStyle: 'bold', fontSize: 7 },
          alternateRowStyles: { fillColor: [249, 250, 251] },
          margin: { left: 12, right: 12 },
          didDrawPage: () => addFooter(doc)
        });
        y = doc.lastAutoTable.finalY + 10;
      }
    }

    addFooter(doc);
    doc.save(filename.endsWith('.pdf') ? filename : filename + '.pdf');
  }

  // ─── Helpers ───
  function addHeader(doc, title, subtitle) {
    const pageW = doc.internal.pageSize.getWidth();
    // Green header bar
    doc.setFillColor(5, 150, 105);
    doc.rect(0, 0, pageW, 24, 'F');
    doc.setFontSize(14);
    doc.setFont(undefined, 'bold');
    doc.setTextColor(255);
    doc.text(title, 12, 11);
    doc.setFontSize(8);
    doc.setFont(undefined, 'normal');
    doc.text(subtitle || 'Microfinancial Management System I', 12, 17);
    // Date on right
    doc.setFontSize(7);
    const dateStr = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    doc.text(dateStr, pageW - 12, 11, { align: 'right' });
  }

  function addFooter(doc) {
    const pageH = doc.internal.pageSize.getHeight();
    const pageW = doc.internal.pageSize.getWidth();
    const pageNum = doc.internal.getNumberOfPages();
    doc.setFontSize(7);
    doc.setTextColor(156, 163, 175);
    doc.text('Microfinancial Management System I — Confidential', 12, pageH - 6);
    doc.text('Page ' + doc.internal.getCurrentPageInfo().pageNumber + ' of ' + pageNum, pageW - 12, pageH - 6, { align: 'right' });
  }

  function downloadBlob(blob, filename) {
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url; a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  // ─── Export prompt (SweetAlert) ───
  function showExportDialog(title, onPDF, onCSV) {
    Swal.fire({
      title: 'Export ' + title,
      html: `<p style="color:#6B7280;font-size:13px;margin-bottom:16px">Choose export format</p>
        <div style="display:flex;gap:12px;justify-content:center">
          <button id="swal-pdf-btn" class="swal2-confirm swal2-styled" style="background:#DC2626;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600">
            📄 PDF
          </button>
          <button id="swal-csv-btn" class="swal2-confirm swal2-styled" style="background:#059669;padding:10px 24px;border-radius:8px;font-size:13px;font-weight:600">
            📊 CSV
          </button>
        </div>`,
      showConfirmButton: false,
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      cancelButtonColor: '#6B7280',
      didOpen: () => {
        document.getElementById('swal-pdf-btn').addEventListener('click', () => { Swal.close(); onPDF(); });
        document.getElementById('swal-csv-btn').addEventListener('click', () => { Swal.close(); onCSV(); });
      }
    });
  }

  return { exportCSV, exportPDF, exportDashboardPDF, showExportDialog };
})();
