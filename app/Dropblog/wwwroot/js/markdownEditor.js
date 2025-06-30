window.markdownEditor = {
    insertMarkdown: function (elementId, before, after) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        let selectedText = textarea.value.substring(start, end);
        
        // Trim spaces from selected text
        const trimmedText = selectedText.trim();
        const leadingSpaces = selectedText.length - selectedText.trimStart().length;
        const trailingSpaces = selectedText.length - selectedText.trimEnd().length;
        
        let newText;
        let newCursorPos;
        let newSelectionStart, newSelectionEnd;
        
        if (trimmedText) {
            // Text is selected - wrap the trimmed text
            if (before === '[link text](url)') {
                // Smart link handling
                if (this.isURL(trimmedText)) {
                    // If selected text is a URL, put it in the URL part
                    newText = '[link text](' + trimmedText + ')';
                    newSelectionStart = start + leadingSpaces + 1; // Position at start of "link text"
                    newSelectionEnd = start + leadingSpaces + 10; // Select "link text"
                } else {
                    // If selected text is not a URL, put it in the text part
                    newText = '[' + trimmedText + '](url)';
                    newSelectionStart = start + leadingSpaces + trimmedText.length + 3; // Position at "url"
                    newSelectionEnd = start + leadingSpaces + trimmedText.length + 6; // Select "url"
                }
                newCursorPos = newSelectionEnd;
            } else if (before === '```') {
                // Code block with selected text - put text on new line
                newText = '```\n' + trimmedText + '\n```';
                newCursorPos = start + leadingSpaces + 4 + trimmedText.length + 1; // After the code text
            } else {
                // Regular formatting - wrap trimmed text
                newText = before + trimmedText + after;
                newCursorPos = start + leadingSpaces + before.length + trimmedText.length + after.length;
            }
        } else {
            // No selection - insert template with cursor positioned inside
            if (before === '**' || before === '*' || before === '<u>') {
                // For formatting, position cursor between markers
                newText = before + after;
                newCursorPos = start + before.length;
            } else if (before === '`') {
                // Inline code
                newText = '`code`';
                newCursorPos = start + 1; // Position cursor at start of "code"
                newSelectionStart = start + 1;
                newSelectionEnd = start + 5; // Select "code"
            } else if (before === '```') {
                // Code block
                newText = '```\ncode\n```';
                newCursorPos = start + 4; // Position cursor after first newline
                newSelectionStart = start + 4;
                newSelectionEnd = start + 8; // Select "code"
            } else if (before === '[link text](url)') {
                // For links, select "link text" part
                newText = '[link text](url)';
                newCursorPos = start + 1;
                newSelectionStart = start + 1;
                newSelectionEnd = start + 10; // Select "link text"
            } else {
                newText = before;
                newCursorPos = start + before.length;
            }
        }
        
        // Replace selected text or insert at cursor
        const adjustedStart = start + leadingSpaces;
        const adjustedEnd = end - trailingSpaces;
        textarea.value = textarea.value.substring(0, adjustedStart) + newText + textarea.value.substring(adjustedEnd);
        
        // Set cursor position or selection
        textarea.focus();
        if (newSelectionStart !== undefined && newSelectionEnd !== undefined) {
            textarea.setSelectionRange(newSelectionStart, newSelectionEnd);
        } else {
            textarea.setSelectionRange(newCursorPos, newCursorPos);
        }
        
        // Trigger input event to update Blazor binding
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    },

    insertCode: function (elementId, isBlock = false, language = '') {
        if (isBlock) {
            if (language) {
                this.insertMarkdown(elementId, '```' + language, '');
            } else {
                this.insertMarkdown(elementId, '```', '');
            }
        } else {
            this.insertMarkdown(elementId, '`', '`');
        }
    },

    insertCodeWithLanguage: function (elementId, language = '') {
        const textarea = document.getElementById(elementId);
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        let selectedText = textarea.value.substring(start, end);
        
        // Trim spaces from selected text
        const trimmedText = selectedText.trim();
        const leadingSpaces = selectedText.length - selectedText.trimStart().length;
        
        let newText;
        let newSelectionStart, newSelectionEnd;
        
        if (trimmedText) {
            // Text is selected - put text on new line with language
            newText = '```' + language + '\n' + trimmedText + '\n```';
            newSelectionStart = start + leadingSpaces + 3; // Position at start of language
            newSelectionEnd = start + leadingSpaces + 3 + language.length; // Select language
        } else {
            // No selection - insert template with language
            newText = '```' + language + '\ncode\n```';
            if (language) {
                newSelectionStart = start + 4 + language.length; // Position after language and newline
                newSelectionEnd = start + 8 + language.length; // Select "code"
            } else {
                newSelectionStart = start + 3; // Position at language spot
                newSelectionEnd = start + 3; // Cursor at language spot
            }
        }
        
        // Replace selected text or insert at cursor
        const adjustedStart = start + leadingSpaces;
        const adjustedEnd = end - (selectedText.length - selectedText.trimEnd().length);
        textarea.value = textarea.value.substring(0, adjustedStart) + newText + textarea.value.substring(adjustedEnd);
        
        // Set cursor position or selection
        textarea.focus();
        textarea.setSelectionRange(newSelectionStart, newSelectionEnd);
        
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

    isURL: function (text) {
        try {
            new URL(text);
            return true;
        } catch {
            // Also check for common URL patterns without protocol
            return /^(www\.|[a-zA-Z0-9-]+\.[a-zA-Z]{2,})/.test(text) || 
                   /^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/.test(text);
        }
    },

    getSelectionInfo: function (elementId) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return { start: 0, end: 0, selectedText: '' };
        
        return {
            start: textarea.selectionStart,
            end: textarea.selectionEnd,
            selectedText: textarea.value.substring(textarea.selectionStart, textarea.selectionEnd)
        };
    },

    // Force update the textarea value for Blazor binding
    forceUpdate: function (elementId) {
        const textarea = document.getElementById(elementId);
        if (!textarea) return;
        
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
        textarea.dispatchEvent(new Event('change', { bubbles: true }));
    }
}; 