<http://kohanaframework.org/guide/using.messages>

Add that message files can be in subfolders, and you can use dot notation to retreive an array path: `Kohana::message('folder/subfolder/file','array.subarray.key')`

Also reinforce that messages are merged by the cascade, not overwritten.