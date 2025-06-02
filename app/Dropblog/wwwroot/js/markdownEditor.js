window.markdownEditor = {
    insertMarkdown: function (elementId, before, after) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        
        let newText;
        let newCursorPos;
        
        if (selectedText) {
            // Text is selected - wrap it
            newText = before + selectedText + after;
            newCursorPos = start + before.length + selectedText.length + after.length;
        } else {
            // No selection - insert template with cursor positioned inside
            if (before === '**' || before === '*' || before === '<u>') {
                // For formatting, position cursor between markers
                newText = before + after;
                newCursorPos = start + before.length;
            } else if (before === '[link text](url)') {
                // For links, select "link text" part
                newText = '[link text](url)';
                newCursorPos = start + 1; // Position cursor at start of "link text"
            } else {
                newText = before;
                newCursorPos = start + before.length;
            }
        }
        
        // Replace selected text or insert at cursor
        textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);
        
        // Set cursor position
        textarea.focus();
        if (before === '[link text](url)' && !selectedText) {
            // For links, select "link text"
            textarea.setSelectionRange(start + 1, start + 10); // Select "link text"
        } else {
            textarea.setSelectionRange(newCursorPos, newCursorPos);
        }
        
        // Trigger input event to update Blazor binding
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    },

    toggleList: function (elementId) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return;

        const start = textarea.selectionStart;
        const value = textarea.value;
        
        // Find the start of the current line
        let lineStart = start;
        while (lineStart > 0 && value[lineStart - 1] !== '\n') {
            lineStart--;
        }
        
        // Find the end of the current line
        let lineEnd = start;
        while (lineEnd < value.length && value[lineEnd] !== '\n') {
            lineEnd++;
        }
        
        const currentLine = value.substring(lineStart, lineEnd);
        let newLine;
        let newCursorPos;
        
        if (currentLine.startsWith('- ')) {
            // Remove list formatting
            newLine = currentLine.substring(2);
            newCursorPos = start - 2;
        } else if (currentLine.startsWith('-')) {
            // Add space after dash
            newLine = '- ' + currentLine.substring(1);
            newCursorPos = start + 1;
        } else {
            // Add list formatting
            newLine = '- ' + currentLine;
            newCursorPos = start + 2;
        }
        
        // Replace the line
        textarea.value = value.substring(0, lineStart) + newLine + value.substring(lineEnd);
        
        // Set cursor position
        textarea.focus();
        textarea.setSelectionRange(Math.max(0, newCursorPos), Math.max(0, newCursorPos));
        
        // Trigger input event
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    },

    getSelectionInfo: function (elementId) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return { start: 0, end: 0, selectedText: '' };
        
        return {
            start: textarea.selectionStart,
            end: textarea.selectionEnd,
            selectedText: textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
        };
    }
}; 