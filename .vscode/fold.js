const vscode = require('vscode');

function activate(context) {
    const foldCommand = vscode.commands.registerCommand('extension.foldSelection', async () => {
        const editor = vscode.window.activeTextEditor;
        if (editor) {
            editor.selection = new vscode.Selection(editor.selection.start, editor.selection.end);
            vscode.commands.executeCommand('editor.fold');
        }
    });

    context.subscriptions.push(foldCommand);
}

function deactivate() {}

module.exports = {
    activate,
    deactivate
};