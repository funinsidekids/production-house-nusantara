# Move Mania! for Resolume Arena

A random movement & effects tool inspired by the popular MOVE MANIA! plugin.

## Features

- **Position, Scale, Rotation & Opacity** - Customizable independently with limitations & speed differences
- **Beat Randomizer** - Activate manually, by speed slider, or by composition BPM
- **Smoothing & Freezing** - Smooth transitions or freeze on specific frames
- **Echo Effects** - Apply echo trails even when movements are disabled
- **Optimized for transparent/cropped sources**

## Quick Start

1. Copy `src/MoveMania.effect` to your Resolume effects folder:
   - **Windows**: `Documents\Resolume\Arena\[version]\effects\`
   - **Mac**: `~/Documents/Resolume/Arena/[version]/effects/`

2. Restart Resolume Arena

3. Find "Move Mania!" in the Effects panel under "Transform"

4. Apply to a clip and adjust parameters

## Documentation

- [Complete User Guide](docs/README.md) - Full feature documentation and parameter reference
- [Installation Guide](build/INSTALL.md) - Detailed installation instructions
- [Effect Format Reference](docs/EFFECT_FORMAT.md) - Learn how Resolume effects work

## Included Presets

1. **Gentle Float** - Subtle, smooth movement for ambient visuals
2. **Chaotic Jump** - High-energy, beat-synced chaos
3. **Echo Trail** - Motion trails without movement
4. **Strobe Effect** - Rhythmic opacity pulsing

## Requirements

- Resolume Arena 7.0+
- OpenGL 3.3+ compatible GPU
- Windows 10/11 or macOS 10.15+

## Project Structure

```
resolume-move-mania/
├── src/
│   └── MoveMania.effect      # Main plugin file
├── docs/
│   ├── README.md             # User documentation
│   └── EFFECT_FORMAT.md      # Technical reference
├── build/
│   └── INSTALL.md            # Installation guide
└── shaders/                   # Additional shaders (if needed)
```

## License

This plugin is provided as-is for use in Resolume Arena.

## Credits

Inspired by MOVE MANIA! concept for Resolume Arena.  
Built for the VJ community.

---

**Enjoy creating with Move Mania!** 🎨✨
