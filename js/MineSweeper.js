// JavaScript source code
var ms = {
	ge: function (t) {
		return document.getElementById(t);
	},
	ce: function (t) {
		return document.createElement(t);
	},
	cea: function (t, p) {
		var e = ms.ce(t);
		p.appendChild(e);
		return e;
	},
	ga: function (n, a) {
		return n.getAttribute(a);
	},
	sa: function (n, a, v) {
		n.setAttribute(a, v);
	},
	rc: function (n) {
		while (n.firstChild) {
			n.removeChild(n.firstChild);
		}
	}
};

class MineSweeper {

	constructor(w,h,tag) {
		this.w = w;
		this.h = h;
		this.tag = null;
		this.cellsurround = [];
		this.tops = [];
		this.bottoms = [];
		this.lefts = [];
		this.rights = [];

		if (typeof (tag) == "string") {
			let v = document.getElementById(tag);
			if (v)
				this.tag = v;
		}
		else
			this.tag = tag;

		this.grid = [];
		for (let i = 0; i < this.w * this.h; i++) {
			this.grid[i] = 16;
		}

		//Build surrounding
		for (let i = 0; i < this.w * this.h; i++) {
			let s = [[-1, -1], [-1, 0], [-1, 1], [0, -1], [0, 1], [1, -1], [1, 0], [1, 1]];
			let sr = [[-1, -1], [-1, 0], [0, -1], [1, -1], [1, 0]];
			let sl = [[-1, 0], [-1, 1],[0, 1], [1, 0], [1, 1]];
			let a = [];
			let row = Math.floor(i / this.w);
			let col = Math.floor(i % this.w);

			//All surrounding
			let ss = s;
			if ((i % this.w) == 0)
				ss = sl;
			if ((i % this.w) == this.w - 1)
				ss = sr;

			for (let x of ss) {
				let v = this.getCellIdx(row + x[0], col+x[1]);
				if (v !== null)
					a.push(v);
			}
			this.cellsurround[i] = a;

			//Tops
			a = [];
			s = [[-1, -1], [-1, 0], [-1, 1]];
			for (let x of s) {
				let v = this.getCellIdx(row + x[0], col + x[1]);
				if (v !== null)
					a.push(v);
			}
			this.tops[i] = a;

			//Bottoms
			a = [];
			s = [[1, -1], [1, 0], [1, 1]];
			for (let x of s) {
				let v = this.getCellIdx(row + x[0], col + x[1]);
				if (v !== null)
					a.push(v);
			}
			this.bottoms[i] = a;

			//Lefts
			a = [];
			s = [[-1, -1], [0, -1], [1, -1]];
			for (let x of s) {
				let v = this.getCellIdx(row + x[0], col + x[1]);
				if (v !== null)
					a.push(v);
			}
			this.lefts[i] = a;

			//Rights
			a = [];
			s = [[-1, 1], [0, 1], [1, 1]];
			for (let x of s) {
				let v = this.getCellIdx(row + x[0], col + x[1]);
				if (v !== null)
					a.push(v);
			}
			this.rights[i] = a;

	   }

		this.mode = 0;
		this.display();

	}

	display() {
		//Delete all content
		ms.rc(this.tag);

		//Build grid
		for (let i = 0; i < this.w * this.h; i++) {
			if (i % this.w == 0)
				ms.cea("br", this.tag);
			let c = ms.cea("div", this.tag);
			c.className = "cell";
			ms.sa(c, "onclick", "hitme(this)");
			ms.sa(c, "_idx", i);
			if (this.grid[i] < 10) {
				c.className = "s_open";
				if (this.grid[i] > 0 && this.grid[i] < 9) {
					c.innerHTML = this.grid[i];
					c.className += (" _" + this.grid[i]);
				}
				if (this.grid[i] == 9) {
					c.className = "s_mine";
					c.innerHTML = "&#9872";
				}
			}
		}


	}

	getCellIdx(r, c) {
		if (r >= 0 && c >= 0) {
			let v = (r * this.w) + c;
			if (v >= 0 && v < (this.w * this.h))
				return v;
		}
		return null;
	}

	setMode(m) {
		this.mode = m;
	}

	setCell(cell, v) {
		this.grid[cell] = v;
		this.display();
	}

	hitme(n,v) {
		let idx = parseInt(ms.ga(n, "_idx"));
		if (this.mode == "set")
			this.setCell(idx, v);
		if (this.mode == "puz") {
			let v = this.grid[idx];
			if (v >= 16)
				this.setCell(idx, (v - 16));
		}
	}

	gridCountMines(cell) {
		let cnt = 0;
		let s = this.cellsurround[cell];
		for (let c of s) {
			if (this.grid[c] == 9 || this.grid[c] == 10)
				cnt++;
		}
		return cnt;
	}

	gridCountHidden(cell) {
		let cnt = 0;
		let s = this.cellsurround[cell];
		for (let c of s) {
			if (this.grid[c] >= 16)
				cnt++;
		}
		return cnt;
	}

	allSideHidden(cell, m) {
		let cnt = 0;
		let s = m[cell];
		for (let c of s) {
			if (this.grid[c] >= 16)
				cnt++;
		}
		if (cnt == 3)
			return true;
		return false;

	}

	leftRightIsOne(cell) {
		let l = cell - 1;
		let r = cell + 1;
		if (l >= 0 && l < (this.w * this.h) && r >= 0 && r < (this.w * this.h)) {
			if (this.grid[l] == 1 && this.grid[r] == 1)
				return true;
		}
		return false;
	}

	topBottomIsOne(cell) {
		let t = cell - this.w;
		let b = cell + this.w;
		if (t >= 0 && t < (this.w * this.h) && b >= 0 && b < (this.w * this.h)) {
			if (this.grid[t] == 1 && this.grid[b] == 1)
				return true;
		}
		return false;

	}

	flagOutSides(cell, m) {
		this.grid[m[cell] [0] ] = 9;
		this.grid[m[cell] [2] ] = 9;
	}

	unhideHidden(cell) {
		let s = this.cellsurround[cell];
		for (let c of s) {
			if (this.grid[c] >= 16)
				this.grid[c] = this.grid[c] - 16;
		}
	}

	unhideHiddenAll(cell) {
		let cnt = 0;
		let s = this.cellsurround[cell];
		for (let c of s) {
			if (this.grid[c] >= 16) {
				this.grid[c] = this.grid[c] - 16;
				cnt++;
			}
		}
		if (cnt > 0)
			return true;
		return false;
	}

	markHiddenasMines(cell) {
		let s = this.cellsurround[cell];
		for (let c of s) {
			if (this.grid[c] >= 16)
				this.grid[c] = 9;
		}
	}

	solveOne() {
		let solved = false;
		for (let i = 0; i < this.w * this.h; i++) {
			if (this.grid[i] == 0) {
				if (this.unhideHiddenAll(i)) {
					this.display();
					solved = true;
					break;
				}
			}
			if (this.grid[i] > 0) {
				let mines = this.gridCountMines(i);
				let hidden = this.gridCountHidden(i);
				if (mines == this.grid[i] && hidden > 0) {
					//We can unhide all hidden
					console.log("Unhide hidden cell [" + i + "] mines = " + mines + " hidden = " + hidden);
					this.unhideHidden(i);
					this.display();
					solved = true;
					break;
				}
				if ((mines+hidden) == this.grid[i] && mines < this.grid[i]) {
					console.log("Mark as mines cell [" + i + "] mines = " + mines + " hidden = " + hidden);
					this.markHiddenasMines(i);
					this.display();
					solved = true;
					break;
				}
				if (this.grid[i] == 2 && hidden == 3) {
					if (this.allSideHidden(i, this.tops)) {
						if (this.leftRightIsOne(i)) {
							this.flagOutSides(i,this.tops);
							this.display();
							solved = true;
							break;
						}
					}
					if (this.allSideHidden(i, this.bottoms)) {
						if (this.leftRightIsOne(i)) {
							this.flagOutSides(i,this.bottoms);
							this.display();
							solved = true;
							break;
						}
					}
					if (this.allSideHidden(i, this.lefts)) {
						if (this.topBottomIsOne(i)) {
							this.flagOutSides(i,this.lefts);
							this.display();
							solved = true;
							break;
						}
					}
					if (this.allSideHidden(i, this.rights)) {
						if (this.topBottomIsOne(i)) {
							this.flagOutSides(i,this.rights);
							this.display();
							solved = true;
							break;
						}
					}
				}
			}
		}
		return solved;
	}

	createRandom(n) {
		let cnt = 0;
		for (let i = 0; i < this.w * this.h; i++) {
			this.grid[i] = 16;
		}
		let tot = this.w * this.h;
		while (cnt < n) {
			let c = Math.floor(Math.random() * tot, 1);
			if (c < tot && this.grid[c] == 16) {
				this.grid[c] = (16+9);
				cnt++;
			}
		}

		//Now create the hidden numbers
		for (let i = 0; i < this.w * this.h; i++) {
			cnt = 0;
			if (this.grid[i] == 16) {
				let s = this.cellsurround[i];
				for (let x of s) {
					if (this.grid[x] == 25)
						cnt++;
				}
				this.grid[i] = (16 + cnt);
			}
		}
	}

	unhideAll() {
		for (let i = 0; i < this.w * this.h; i++) {
			this.grid[i] = this.grid[i] - 16;
		}
	}
}