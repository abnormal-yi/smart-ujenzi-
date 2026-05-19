import fs from 'fs';
import path from 'path';

function addReactImport(filePath) {
  let content = fs.readFileSync(filePath, 'utf8');
  if (content.includes('React.') && !content.includes("import React ")) {
    content = "import React from 'react';\n" + content;
    fs.writeFileSync(filePath, content, 'utf8');
    console.log('Added React to', filePath);
  }
}

const dir = path.join(process.cwd(), 'src', 'pages');
fs.readdirSync(dir).forEach(file => {
  if (file.endsWith('.tsx')) {
    addReactImport(path.join(dir, file));
  }
});
addReactImport(path.join(process.cwd(), 'src', 'App.tsx'));

