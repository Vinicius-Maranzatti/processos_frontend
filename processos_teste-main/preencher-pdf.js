const { PDFDocument } = require('pdf-lib');
const fs = require('fs');
const path = require('path');

async function fillForm(pdfPath, outputPath, data) {
    try {
        const pdfBytes = fs.readFileSync(pdfPath);
        const pdfDoc = await PDFDocument.load(pdfBytes);
        const form = pdfDoc.getForm();

        // Listar todos os campos do formulário
        const fields = form.getFields();
        fields.forEach((field, index) => {
            console.log(`Campo ${index + 1}: ${field.getName()}`);
        });

        if (form.getFields().length === 0) {
            console.warn(`O arquivo ${pdfPath} não contém campos de formulário. Copiando sem modificações.`);
            fs.copyFileSync(pdfPath, outputPath);
            return;
        }

        let camposPreenchidos = 0;
        for (const [field, value] of Object.entries(data)) {
            try {
                if (form.getField(field)) {
                    form.getTextField(field).setText(value);
                    console.log(`Campo preenchido: ${field} = ${value}`);
                    camposPreenchidos++;
                } else {
                    console.warn(`Campo não encontrado: ${field}`);
                }
            } catch (error) {
                console.warn(`Erro ao preencher o campo ${field}:`, error);
            }
        }

        if (camposPreenchidos === 0) {
            console.warn(`Nenhum campo foi preenchido no arquivo ${pdfPath}. Copiando sem modificações.`);
            fs.copyFileSync(pdfPath, outputPath);
            return;
        }

        const modifiedPdfBytes = await pdfDoc.save();
        fs.writeFileSync(outputPath, modifiedPdfBytes);
        console.log(`PDF preenchido salvo em: ${outputPath}`);
    } catch (error) {
        console.error(`Erro ao preencher o PDF ${pdfPath}:`, error);
        throw error;
    }
}

async function processarPDFs(pastaSelecionada, pastaDestino, dadosFormulario) {
    try {
        const arquivosPDF = fs.readdirSync(pastaSelecionada).filter(file => file.endsWith('.pdf'));

        if (arquivosPDF.length === 0) {
            console.log('Nenhum arquivo PDF encontrado na pasta:', pastaSelecionada);
            return [];
        }

        const arquivosProcessados = [];

        for (const arquivo of arquivosPDF) {
            const pdfPath = path.join(pastaSelecionada, arquivo);
            const outputPath = path.join(pastaDestino, arquivo);

            await fillForm(pdfPath, outputPath, dadosFormulario);

            const conteudoPdf = fs.readFileSync(outputPath);
            const conteudoBase64 = conteudoPdf.toString('base64');

            arquivosProcessados.push({
                nome: arquivo,
                conteudo: conteudoBase64,
                tipo: 'pdf'
            });
        }

        console.log('Processamento de PDFs concluído com sucesso.');
        return arquivosProcessados;
    } catch (error) {
        console.error('Erro no processamento dos PDFs:', error);
        throw error;
    }
}

const jsonFile = process.argv[2];
if (!jsonFile) {
    console.error('Arquivo JSON não fornecido.');
    process.exit(1);
}

const dados = JSON.parse(fs.readFileSync(jsonFile, 'utf-8'));

processarPDFs(dados.pasta_selecionada, dados.pasta_destino, dados.dados_formulario)
    .then(arquivosProcessados => {
        fs.writeFileSync(jsonFile + '_output', JSON.stringify(arquivosProcessados));
    })
    .catch(error => {
        console.error('Erro ao processar PDFs:', error);
        process.exit(1);
});