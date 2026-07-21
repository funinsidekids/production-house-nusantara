# Build Script for Move Mania! Plugin

This directory contains build artifacts and installation packages.

## Building the Plugin

The main plugin file is `MoveMania.effect` located in the `src/` directory.

### For Windows:
```bash
# Copy to Resolume effects folder
cp src/MoveMania.effect "/c/Users/$USER/Documents/Resolume/Arena/effects/"
```

### For macOS:
```bash
# Copy to Resolume effects folder
cp src/MoveMania.effect ~/Documents/Resolume/Arena/effects/
```

## File Structure

```
resolume-move-mania/
├── src/
│   └── MoveMania.effect      # Main plugin file (XML + GLSL)
├── shaders/                   # Additional shader files (if needed)
├── docs/
│   └── README.md             # User documentation
├── build/                     # Build artifacts
└── INSTALL.md                # This file
```

## Installation Instructions

### Quick Install
1. Locate your Resolume Arena effects folder:
   - **Windows**: `Documents\Resolume\Arena\[version]\effects\`
   - **Mac**: `~/Documents/Resolume/Arena/[version]/effects/`

2. Copy `src/MoveMania.effect` to that folder

3. Restart Resolume Arena

4. Find "Move Mania!" in the Effects panel under "Transform" category

### Alternative: Drag & Drop
Simply drag the `MoveMania.effect` file directly into Resolume's interface.

## Verification

After installation:
1. Open Resolume Arena
2. Go to Effects panel
3. Look for "Move Mania!" under Transform category
4. Apply to a clip to test

## Updating

To update an existing installation:
1. Close Resolume Arena
2. Replace the old `MoveMania.effect` file with the new version
3. Restart Resolume Arena

## Uninstallation

Remove `MoveMania.effect` from your Resolume effects folder.

## Requirements

- Resolume Arena 7.0 or later
- OpenGL 3.3+ compatible GPU
- Windows 10/11 or macOS 10.15+

## Support Files

- `docs/README.md` - Complete user guide
- `src/MoveMania.effect` - Plugin source (can be edited if needed)

## Version History

### v1.0.0
- Initial release
- Position, Scale, Rotation, Opacity controls
- Beat Randomizer (BPM, Speed, Manual modes)
- Echo Effects with multiple blend modes
- 4 built-in presets
- Smoothing and Freeze options
